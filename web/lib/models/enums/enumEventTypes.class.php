<?php
use MyCLabs\Enum\Enum;

/**
 * Class enumEventTypes
 * @method static enumEventTypes Mileage()
 * @method static enumEventTypes Service()
 * @method static enumEventTypes Refueling()
 * @method static enumEventTypes TireChange()
 * @method static enumEventTypes Accident()
 * @method static enumEventTypes Payment()
 * @method static enumEventTypes Sell()
 */
final class enumEventTypes extends Enum {
    private const Mileage = 'Mileage';
    private const Service = 'Service';
    private const Refueling = 'Refueling';
    private const TireChange = 'TireChange';
    private const Accident = 'Accident';
    private const Payment = 'Payment';
    private const Sell = 'Sell';
}