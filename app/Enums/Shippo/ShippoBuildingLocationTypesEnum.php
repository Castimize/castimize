<?php

namespace App\Enums\Shippo;

enum ShippoBuildingLocationTypesEnum: string
{
    case FrontDoor = 'Front Door';
    case BackDoor = 'Back Door';
    case SideDoor = 'Side Door';
    case KnockOnDoor = 'Knock on Door';
    case RingBell = 'Ring Bell';
    case MailRoom = 'Mail Room';
    case Office = 'Office';
    case Reception = 'Reception';
    case InAtMailbox = 'In At Mailbox';
    case SecurityDeck = 'Security Deck';
    case ShippingDock = 'Shipping Dock';
    case Other = 'Other';

    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->value] = $case->name;
        }

        return $values;
    }
}
