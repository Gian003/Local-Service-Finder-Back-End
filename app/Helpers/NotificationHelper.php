<?php
namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    // Send notification to a user
    public static function send(
        int    $userId,
        string $title,
        string $message,
        string $type,
        ?int   $referenceId   = null,
        ?string $referenceType = null,
    ): void {
        Notification::create([
            'user_id'        => $userId,
            'title'          => $title,
            'message'        => $message,
            'type'           => $type,
            'is_read'        => false,
            'reference_id'   => $referenceId,
            'reference_type' => $referenceType,
        ]);
    }

    // Booking confirmed notification
    public static function bookingConfirmed(int $userId, string $serviceName): void
    {
        self::send(
            userId:  $userId,
            title:   'Booking Confirmed!',
            message: "Your booking for {$serviceName} has been confirmed.",
            type:    'booking',
        );
    }

    // Booking accepted by worker
    public static function bookingAccepted(int $userId, string $workerName): void
    {
        self::send(
            userId:  $userId,
            title:   'Booking Accepted!',
            message: "{$workerName} has accepted your booking request.",
            type:    'booking',
        );
    }

    // Booking cancelled
    public static function bookingCancelled(int $userId, string $serviceName): void
    {
        self::send(
            userId:  $userId,
            title:   'Booking Cancelled',
            message: "Your booking for {$serviceName} has been cancelled.",
            type:    'booking',
        );
    }

    // Booking completed
    public static function bookingCompleted(int $userId, string $serviceName): void
    {
        self::send(
            userId:  $userId,
            title:   'Service Completed',
            message: "Your {$serviceName} service is done! How was your experience?",
            type:    'review',
        );
    }

    // Payment successful
    public static function paymentSuccessful(int $userId, string $serviceName, float $amount): void
    {
        self::send(
            userId:  $userId,
            title:   'Payment Successful!',
            message: "You have paid ₱{$amount} for {$serviceName}.",
            type:    'payment',
        );
    }

    // Reminder
    public static function serviceReminder(int $userId, string $serviceName, string $date): void
    {
        self::send(
            userId:  $userId,
            title:   'Reminder',
            message: "Your {$serviceName} service is scheduled for {$date}.",
            type:    'reminder',
        );
    }
}
