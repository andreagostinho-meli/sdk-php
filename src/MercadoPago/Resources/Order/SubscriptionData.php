<?php

/** API version: b950ae02-4f49-4686-9ad3-7929b21b6495 */

namespace MercadoPago\Resources\Order;

use MercadoPago\Serialization\Mapper;

/** Subscription Data class */
class SubscriptionData
{
  /** Class mapper. */
  use Mapper;

  /** Subscription sequence */
  public array|object|null $subscription_sequence;

  /** Invoice id */
  public ?string  $invoice_id;

  /** Invoice period  */
  public array|object|null $invoice_period;

  /** Billing date  */
  public ?string $billing_date;

  private $map = [
    "subscription_sequence" => "MercadoPago\Resources\Order\SubscriptionSequence",
    "invoice_period" => "MercadoPago\Resources\Order\InvoicePeriod",
  ];
  
  /**
  * Method responsible for getting map of entities.
  */
  public function getMap(): array
  {
    return $this->map;
  }
}