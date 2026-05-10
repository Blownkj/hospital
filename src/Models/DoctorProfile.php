<?php
declare(strict_types=1);

namespace App\Models;

/** Immutable DTO for doctor profile data. Use DoctorProfile::fromRow() to hydrate from DB. */
final class DoctorProfile
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $userId,
        public readonly string  $lastName,
        public readonly string  $firstName,
        public readonly ?string $middleName,
        public readonly string  $fullName,
        public readonly string  $email,
        public readonly int     $specializationId,
        public readonly string  $specialization,
        public readonly string  $bio,
        public readonly ?string $photoUrl,
        public readonly float   $avgRating,
        public readonly int     $reviewCount,
        public readonly bool    $isActive,
    ) {}

    public static function fromRow(array $row): self
    {
        $lastName   = (string) ($row['last_name']  ?? '');
        $firstName  = (string) ($row['first_name'] ?? '');
        $middleName = ($row['middle_name'] ?? null) ?: null;
        $fullName   = trim($lastName . ' ' . $firstName . ($middleName ? ' ' . $middleName : ''));

        return new self(
            id:               (int)    ($row['id']               ?? 0),
            userId:           (int)    ($row['user_id']          ?? 0),
            lastName:         $lastName,
            firstName:        $firstName,
            middleName:       $middleName,
            fullName:         $fullName,
            email:            (string) ($row['email']            ?? ''),
            specializationId: (int)    ($row['specialization_id'] ?? 0),
            specialization:   (string) ($row['specialization']   ?? ''),
            bio:              (string) ($row['bio']              ?? ''),
            photoUrl:         $row['photo_url'] ?: null,
            avgRating:        (float)  ($row['avg_rating']       ?? 0),
            reviewCount:      (int)    ($row['review_count']     ?? 0),
            isActive:         (bool)   ($row['is_active']        ?? true),
        );
    }
}
