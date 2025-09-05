<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    // --- CORRECTION : On rétablit la version correcte ---
    case NON_PAYEE = 'non_payee'; 
    
    case PARTIELLEMENT_REGLE = 'partiellement_regle';
    case COMPLETEMENT_REGLE = 'completement_regle';
    case ANNULE = 'annule';

    public function getLabel(): string
    {
        return match ($this) {
            self::NON_PAYEE => 'Non payée',
            self::PARTIELLEMENT_REGLE => 'Partiellement réglé',
            self::COMPLETEMENT_REGLE => 'Complètement réglé',
            self::ANNULE => 'Annulé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NON_PAYEE => 'danger',
            self::PARTIELLEMENT_REGLE => 'warning',
            self::COMPLETEMENT_REGLE => 'success',
            self::ANNULE => 'gray',
        };
    }
}