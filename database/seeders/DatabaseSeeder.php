<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Invoice;
use App\Models\Region;
use App\Models\Server;
use App\Models\Pelanggan;
use App\Models\User;
use Carbon\Carbon;
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
            'password' => bcrypt('Raisya123;')
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
                    'bandwidth' => $bandwidth,
                    'bulanan' => $bandwidth * 100000,
                    'mac' => strtoupper(fake()->lexify('ZTE?????????')),
                    'created_at' => $create,
                    'updated_at' => $create,
                ]);

                Invoice::create([
                    'pelanggan_id' => $pelanggan->id,
                    'pay_at' => now(),
                    'created_at' => Carbon::parse($create)->addMonth(),
                    'updated_at' => Carbon::parse($create)->addMonth(),
                ]);
            }
        }
    }
}
