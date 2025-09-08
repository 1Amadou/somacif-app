<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class GrantAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:grant-admin-role {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Donne les privilèges administrateur à un utilisateur via son email';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $email = $this->argument('email');

        // Validation de l'email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->error('L\'adresse email fournie n\'est pas valide.');
            return;
        }

        // Recherche de l'utilisateur
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Aucun utilisateur trouvé avec l'email : {$email}");
            return;
        }

        // Assignation du rôle
        $user->is_admin = true;
        $user->save();

        $this->info("Le rôle d'administrateur a été accordé avec succès à {$user->name} ({$email}).");
    }
}