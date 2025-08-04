<?php

namespace App\Livewire;

use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $subject = 'Question générale';
    public string $message = '';
    public bool $isSent = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'phone' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string|min:10',
        ];
    }

    public function submit()
    {
        $this->validate();

        // Logique d'envoi de l'email à l'administrateur (à implémenter)
        // Mail::to('admin@somacif.com')->send(new ContactMessage($this->name, $this->phone, $this->subject, $this->message));

        $this->isSent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}