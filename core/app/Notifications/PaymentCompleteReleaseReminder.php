<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentCompleteReleaseReminder extends Notification
{
    use Queueable;

    public $escrow;
    public $totalAmount;
    public $listingTitle;

    /**
     * Create a new notification instance.
     */
    public function __construct($escrow, $totalAmount, $listingTitle = null)
    {
        $this->escrow = $escrow;
        $this->totalAmount = $totalAmount;
        $this->listingTitle = $listingTitle ?? ($escrow->listing ? $escrow->listing->title : $escrow->title);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment Complete - Release Funds',
            'message' => 'You have paid the full amount of ' . $this->totalAmount . ' for "' . $this->listingTitle . '". Please release the funds to the seller once the transaction is complete.',
            'click_url' => route('user.escrow.details', $this->escrow->id),
            'type' => 'payment_complete_release_reminder',
            'escrow_id' => $this->escrow->id,
            'escrow_number' => $this->escrow->escrow_number,
        ];
    }
}

