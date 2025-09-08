<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDEE = 'validee';
    case EN_PREPARATION = 'en_preparation';
    case EN_COURS_LIVRAISON = 'en_cours_livraison';
    case LIVREE = 'livree';
    case ANNULEE = 'annulee';

    /**
     * Retourne le libellé en français pour l'affichage.
     * C'est utile pour les interfaces utilisateur (admin, client, etc.).
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::VALIDEE => 'Validée',
            self::EN_PREPARATION => 'En préparation',
            self::EN_COURS_LIVRAISON => 'En cours de livraison',
            self::LIVREE => 'Livrée',
            self::ANNULEE => 'Annulée',
        };
    }

    /**
     * Retourne une couleur associée à chaque statut.
     * C'est pratique pour l'affichage dans le panel d'administration.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'gray',
            self::VALIDEE => 'success',
            self::EN_PREPARATION => 'warning',
            self::EN_COURS_LIVRAISON => 'info',
            self::LIVREE => 'primary',
            self::ANNULEE => 'danger',
        };
    }
}