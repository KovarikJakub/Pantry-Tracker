<?php

use App\Models\Category;
use App\Models\Household;
use App\Models\Shelf;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: Household, 2: Shelf, 3: Category}
 */
function memberWithShelf(float $capacity = 5000): array
{
    [$member, $household] = householdWithMember('member');
    $shelf = $household->appliances()->create(['name' => 'Fridge', 'appliance_type' => 'fridge'])
        ->shelves()->create(['label' => 'Top', 'shelf_order' => 1, 'max_load_capacity_g' => $capacity]);

    return [$member, $household, $shelf, Category::query()->create(['name' => 'Dairy'])];
}

it('finds default catalogue products by a part of their name and shows their category', function () {
    [$member, $household, $shelf, $dairy] = memberWithShelf();
    catalogueProduct($dairy, 'Greek yoghurt');
    catalogueProduct($dairy, 'Cheddar');

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->set('term', 'YOGH')
        ->assertSee('Greek yoghurt')
        ->assertSee('Dairy')
        ->assertDontSee('Cheddar');
});

it('caps the results and ignores terms that are too short', function () {
    [$member, $household, $shelf, $dairy] = memberWithShelf();

    foreach (range(1, 10) as $number) {
        catalogueProduct($dairy, "Milk {$number}");
    }

    $this->withSession(['user_id' => $member->id]);

    $component = Livewire::test('product.searchbar', ['household' => $household])
        ->set('term', 'M')
        ->assertDontSee('Milk');

    expect($component->set('term', 'Milk')->instance()->results)->toHaveCount(8);
});

it('treats like wildcards in the term literally', function () {
    [$member, $household, $shelf, $dairy] = memberWithShelf();
    catalogueProduct($dairy, 'Cheddar');

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->set('term', '%%')
        ->assertDontSee('Cheddar')
        ->assertSee('No products found.');
});

it('adds the selected product with its default price and expiry', function () {
    [$member, $household, $shelf, $dairy] = memberWithShelf();
    $product = catalogueProduct($dairy, 'Cheddar');
    $product->update(['default_price' => 65, 'default_expiry_days' => 30]);

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->set('term', 'Ched')
        ->call('add', $product->id, $shelf->id)
        ->assertHasNoErrors()
        ->assertSet('term', '')
        ->assertDispatched('food-added', shelfId: $shelf->id);

    expect($shelf->foodItems()->sole())
        ->product_id->toBe($product->id)
        ->registered_by_user_id->toBe($member->id)
        ->status->toBe('stored')
        ->purchase_price->toBe('65.00')
        ->and($shelf->foodItems()->sole()->expiration_date->toDateString())->toBe(today()->addDays(30)->toDateString());
});

it('shows an error and adds nothing when the product does not fit', function () {
    [$member, $household, $shelf, $dairy] = memberWithShelf(capacity: 100);
    $product = catalogueProduct($dairy, 'Cheddar');

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->set('term', 'Ched')
        ->call('add', $product->id, $shelf->id)
        ->assertHasErrors('shelf_id')
        ->assertSee('Cheddar does not fit on Top.')
        ->assertSet('term', 'Ched');

    expect($shelf->foodItems()->exists())->toBeFalse();
});

it('forbids an outsider from adding food', function () {
    [, $household, $shelf, $dairy] = memberWithShelf();
    $product = catalogueProduct($dairy, 'Cheddar');

    $this->withSession(['user_id' => User::factory()->create()->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->call('add', $product->id, $shelf->id)
        ->assertForbidden();

    expect($shelf->foodItems()->exists())->toBeFalse();
});

it('shows the new row only on the shelf that received the food', function () {
    [$member, $household, $shelf, $dairy] = memberWithShelf();
    $product = catalogueProduct($dairy, 'Cheddar');

    $this->withSession(['user_id' => $member->id]);

    $card = Livewire::test('shelf.card', ['household' => $household, 'shelf' => $shelf])
        ->assertSee('Empty shelf.');

    Livewire::test('product.searchbar', ['household' => $household])
        ->call('add', $product->id, $shelf->id);

    $card->dispatch('food-added', shelfId: $shelf->id)
        ->assertSee('Cheddar')
        ->assertDontSee('Empty shelf.');
});

it('shows the default weight and expiry of each result', function () {
    [$member, $household, , $dairy] = memberWithShelf();
    catalogueProduct($dairy, 'Cheddar')->update(['default_expiry_days' => 30]);

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->set('term', 'Ched')
        ->assertSee('500 g')
        ->assertSee('Expires in 30 days');
});

it('refuses a shelf of another household', function () {
    [$member, $household, , $dairy] = memberWithShelf();
    $product = catalogueProduct($dairy, 'Cheddar');
    [, , $foreignShelf] = memberWithShelf();

    $this->withSession(['user_id' => $member->id]);

    Livewire::test('product.searchbar', ['household' => $household])
        ->call('add', $product->id, $foreignShelf->id)
        ->assertNotFound();

    expect($foreignShelf->foodItems()->exists())->toBeFalse();
});

it('renders one search bar for the whole shelves tab', function () {
    [$member, $household, $shelf] = memberWithShelf();
    $shelf->appliance->shelves()->create(['label' => 'Bottom', 'shelf_order' => 2, 'max_load_capacity_g' => 5000]);

    $content = $this->withSession(['user_id' => $member->id])
        ->get(route('households.show', $household->id))
        ->assertOk()
        ->assertSee('Select a shelf below to add products to it.')
        ->getContent();

    expect(substr_count($content, 'role="combobox"'))->toBe(1);
});
