<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Paket;
use App\Models\Region;
use App\Models\Server;
use App\Models\Invoice;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Raisya Wandha Saputri',
            'username' => 'rswdhspri',
            'role' => 1,
            'password' => bcrypt('Raisya123;')
        ]);
        User::create([
            'name' => 'Muhamad Ardalepa',
            'username' => 'buff_oren',
            'role' => 2,
            'password' => bcrypt('Asdasd')
        ]);
        Region::create(['name' => 'Pontianak']);
        Region::create(['name' => 'Ngabang']);
        Server::create([
            'name' => '28 Oktober',
            'kode' => 'S',
            'region_id' => 1
        ]);
        Server::create([
            'name' => 'RBK Raya',
            'kode' => 'R',
            'region_id' => 1
        ]);
        Server::create([
            'name' => 'Kobar',
            'kode' => 'K',
            'region_id' => 1
        ]);
        for ($i = 1; $i <= 5; $i++) {
            Paket::create([
                'bandwidth' => $i,
                'harga' => $i * 100000
            ]);
        }

        foreach (Server::all() as $i => $server) {

            for ($j = 1; $j <= 100; $j++) {
                $bandwidth = random_int(1, 5);
                $create = fake()->dateTimeThisYear();
                $pelanggan = Pelanggan::create([
                    'id' => $server->kode . str_pad($j, 4, "0", STR_PAD_LEFT),
                    'nama' => fake('id_ID')->firstName . ' ' . fake('id_ID')->lastName,
                    'no_telp' => fake()->numerify('628#########'),
                    'email' => fake()->email(),
                    'region_id' => 1,
                    'server_id' => $i + 1,
                    'va' => '0' . ($i + 1) . str_pad($j, 4, "0", STR_PAD_LEFT),
                    'alamat' => fake('id_ID')->address(),
                    'mac' => strtoupper(fake()->lexify('ZTE?????????')),
                    'created_at' => Carbon::parse($create)->subMonth(),
                    'updated_at' => Carbon::parse($create)->subMonth(),
                ]);

                Invoice::create([
                    'pelanggan_id' => $pelanggan->id,
                    'paket_id' => $bandwidth,
                    'pay_at' => now(),
                    'created_at' => $create,
                    'updated_at' => $create,
                ]);
            }
        }
    }
}
