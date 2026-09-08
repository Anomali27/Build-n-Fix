<?php

test('locations page renders successfully', function () {
    $response = $this->get(route('locations.index'));

    $response->assertStatus(200);
    $response->assertSee('Lokasi Build n Fix');
    $response->assertSee('Serdam');
    $response->assertSee('Gajahmada');
    $response->assertSee('Kota Baru');
    $response->assertSee('Belanja Sekarang');
    $response->assertSee(route('categories.index', ['branches' => ['Serdam']]));
    $response->assertSee(route('categories.index', ['branches' => ['Gajahmada']]));
    $response->assertSee(route('categories.index', ['branches' => ['Kota Baru']]));
});

test('hero section link points to locations index', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee(route('locations.index'));
});
