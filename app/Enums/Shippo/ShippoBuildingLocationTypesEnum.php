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
        return [
            self::FrontDoor->value => self::FrontDoor->name,
            self::BackDoor->value => self::BackDoor->name,
            self::SideDoor->value => self::SideDoor->name,
            self::KnockOnDoor->value => self::KnockOnDoor->name,
            self::RingBell->value => self::RingBell->name,
            self::MailRoom->value => self::MailRoom->name,
            self::Office->value => self::Office->name,
            self::Reception->value => self::Reception->name,
            self::InAtMailbox->value => self::InAtMailbox->name,
            self::SecurityDeck->value => self::SecurityDeck->name,
            self::ShippingDock->value => self::ShippingDock->name,
            self::Other->value => self::Other->name,
        ];
    }
}
