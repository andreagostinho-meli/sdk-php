<?php

/** API version: b950ae02-4f49-4686-9ad3-7929b21b6495 */

namespace MercadoPago\Resources\Order\Transaction;

/** PaymentMethod class. */
class PaymentMethod
{
    /** Payment method ID. */
    public ?string $id;

    /** Payment method type. */
    public ?string $type;

    /** Token. */
    public ?string $token;

    /** Installments. */
    public ?int $installments;

    /** Statement descriptor. */
    public ?string $statement_descriptor;
}
