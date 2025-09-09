<?php

namespace App\Livewire;

use App\Models\ContactMessage; 
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use Illuminate\Support\Facades\Notification; 
use Livewire\Component;

class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $phone = ''; 
    public $subject = 'Question générale';
    public $message = '';
    public $isSent = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20', 
        'subject' => 'required|string|max:255',
        'message' => 'required|string|min:10',
    ];

    public function submit()
    {
        $data = $this->validate();

      
        $message = ContactMessage::create($data);

        // Notifier les administrateurs
        $admins = User::where('is_admin', true)->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewContactMessageNotification($message));
        }
        
        $this->isSent = true; 
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}