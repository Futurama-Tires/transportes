<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Operador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedOperadores(20);      // 20 operadores + Users + rol
            $this->seedCapturistas(5);      // 5 capturistas + Users + rol

            // 20 tarjetas y 20 vehículos (1 a 1)
            [$tarjetaIds] = $this->seedTarjetasSiVale(20);
            $this->seedVehiculosConTarjeta($tarjetaIds);
        });
    }

    /**
     * Crea N operadores con su User y asigna rol "operador".
     */
    protected function seedOperadores(int $n = 20): void
    {
        $nombres = ['Juan','María','Luis','Ana','Carlos','Lucía','Miguel','Elena','Jorge','Sofía','Pedro','Paula','Héctor','Diana','Andrés','Valeria','Óscar','Camila','Rubén','Andrea','Iván','Marta'];
        $apellidos = ['García','Hernández','Martínez','López','González','Pérez','Sánchez','Ramírez','Cruz','Flores','Rivera','Mendoza','Ortiz','Vargas','Jiménez','Castro','Rojas','Morales','Núñez','Salazar'];

        for ($i = 1; $i <= $n; $i++) {
            $nombre   = $nombres[array_rand($nombres)];
            $apPat    = $apellidos[array_rand($apellidos)];
            $apMat    = $apellidos[array_rand($apellidos)];
            $fullName = trim("$nombre $apPat $apMat");

            $user = User::create([
                'name'     => $fullName,
                'email'    => "operador{$i}@seed.local",
                'password' => Hash::make('password123'),
            ]);
            $this->grantRole($user, 'operador');

            Operador::create([
                'user_id'                  => $user->id,
                'nombre'                   => $nombre,
                'apellido_paterno'         => $apPat,
                'apellido_materno'         => $apMat,
                'telefono'                 => $this->fakePhone(),
                'contacto_emergencia_nombre'=> $nombres[array_rand($nombres)],
                'contacto_emergencia_tel'  => $this->fakePhone(),
                'tipo_sangre'              => collect(['A+','A-','B+','B-','AB+','AB-','O+','O-'])->random(),
            ]);
        }
    }

    /**
     * Crea N capturistas con su User y asigna rol "capturista".
     * Inserta en la tabla `capturistas` sin depender de un modelo específico.
     */
    protected function seedCapturistas(int $n = 5): void
    {
        $nombres = ['Raúl','Patricia','Fernando','Karla','Emilio','Daniela','Hugo','Adriana','Rafael','Brenda','Mario','Fabiola'];
        $apellidos = ['García','Hernández','Martínez','López','González','Pérez','Sánchez','Ramírez','Cruz','Flores','Rivera','Mendoza'];

        for ($i = 1; $i <= $n; $i++) {
            $nombre   = $nombres[array_rand($nombres)];
            $apPat    = $apellidos[array_rand($apellidos)];
            $apMat    = $apellidos[array_rand($apellidos)];
            $fullName = trim("$nombre $apPat $apMat");

            $user = User::create([
                'name'     => $fullName,
                'email'    => "capturista{$i}@seed.local",
                'password' => Hash::make('password123'),
            ]);
            $this->grantRole($user, 'capturista');

            DB::table('capturistas')->insert([
                'user_id'          => $user->id,
                'nombre'           => $nombre,
                'apellido_paterno' => $apPat,
                'apellido_materno' => $apMat,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    /**
     * Crea N tarjetas SiVale en la tabla `tarjetassivale`.
     *
     * @return array{0: array<int,int>} Lista/array de IDs insertados.
     */
    protected function seedTarjetasSiVale(int $n = 20): array
    {
        $ids = [];
        for ($i = 1; $i <= $n; $i++) {
            // 16 dígitos únicos (evita chocar con existentes)
            $numero = $this->makeCardNumber(5200000000000000 + $i);

            $id = DB::table('tarjetassivale')->insertGetId([
                'numero_tarjeta'   => $numero,
                'nip'              => str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                'fecha_vencimiento'=> now()->addYears(random_int(3, 7))->toDateString(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $ids[] = $id;
        }
        return [$ids];
    }

    /**
     * Crea 1 vehículo por cada tarjeta SiVale, dejando la relación 1–a–1.
     */
    protected function seedVehiculosConTarjeta(array $tarjetaIds): void
    {
        $marcas = ['NISSAN','VW','TOYOTA','FORD','CHEVROLET','RENAULT','HONDA','MAZDA'];
        $unidades = ['TSURU','NP300','SAVEIRO','SPRINTER','RANGER','AVEO','MARCH','VERSA','GOL','POINTER'];
        $ubicaciones = ['CVC','QRO','CDMX','CENTRO'];
        $estados = ['EDO MEX','QUERETARO','MORELOS','CIUDAD DE MEXICO'];

        $i = 0;
        foreach ($tarjetaIds as $tarjetaId) {
            $i++;
            $unidad = $unidades[array_rand($unidades)];
            $marca  = $marcas[array_rand($marcas)];

            DB::table('vehiculos')->insert([
                'ubicacion'                => $ubicaciones[array_rand($ubicaciones)],
                'propietario'              => 'CENTRO',
                'unidad'                   => $unidad . ' ' . $i,
                'marca'                    => $marca,
                'anio'                     => random_int(2005, (int)date('Y')),
                'serie'                    => strtoupper(Str::random(17)),                  // único
                'motor'                    => strtoupper(Str::random(12)),
                'placa'                    => 'SEED-' . (1000 + $i),                         // único
                'estado'                   => $estados[array_rand($estados)],
                'vencimiento_t_circulacion'=> now()->addYears(random_int(1, 3))->toDateString(),
                'cambio_placas'            => now()->addMonths(random_int(6, 24))->toDateString(),
                'poliza_hdi'               => 'POL-' . strtoupper(Str::random(8)),
                'poliza_latino'            => null,
                'poliza_qualitas'          => null,
                'kilometros'               => random_int(0, 250_000),
                'tarjeta_si_vale_id'       => $tarjetaId,                                   // relación 1–1
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
        }
    }

    /**
     * Intenta asignar rol con Spatie; si falla, hace insert manual en model_has_roles.
     */
    protected function grantRole(User $user, string $roleName): void
    {
        // Spatie (si está disponible)
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole($roleName);
                return;
            } catch (\Throwable $e) {
                // fallback a inserción manual
            }
        }

        // Fallback: inserción directa en model_has_roles
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');
        if ($roleId) {
            DB::table('model_has_roles')->insert([
                'role_id'    => $roleId,
                'model_type' => User::class,
                'model_id'   => $user->id,
            ]);
        }
    }

    /**
     * Genera un número de tarjeta de 16 dígitos como string.
     */
    protected function makeCardNumber(int $base): string
    {
        $num = (string)$base;
        return str_pad($num, 16, '0', STR_PAD_RIGHT);
    }

    /**
     * Teléfono de prueba con prefijo +52.
     */
    protected function fakePhone(): string
    {
        return '+52' . random_int(1000000000, 9999999999);
    }
}

