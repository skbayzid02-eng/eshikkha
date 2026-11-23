<?php

namespace App\Services;

use App\Models\GuardianStudent;
use App\Models\PushNotification;
use App\Models\Student;
use App\Models\User;

class NotificationService
{
    /**
     * Send a notification to a specific student and automatically to their linked guardians.
     */
    public static function notifyStudent(Student $student, string $title, string $body, string $type, array $data = []): void
    {
        // 1. Notify student user account if exists
        if ($student->user_id) {
            PushNotification::create([
                'institution_id' => $student->institution_id,
                'user_id' => $student->user_id,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data' => $data,
                'is_read' => false,
            ]);
        }

        // 2. Notify all approved linked guardians
        $guardianLinks = GuardianStudent::where('student_id', $student->id)
            ->where('status', 'approved')
            ->get();

        foreach ($guardianLinks as $link) {
            PushNotification::create([
                'institution_id' => $student->institution_id,
                'user_id' => $link->guardian_user_id,
                'title' => $title,
                'body' => "({$student->name}) ".$body,
                'type' => $type,
                'data' => array_merge($data, ['student_id' => $student->id, 'student_name' => $student->name]),
                'is_read' => false,
            ]);
        }
    }

    /**
     * Send notification to a specific user (e.g. guardian, teacher, student).
     */
    public static function notifyUser(User $user, string $title, string $body, string $type, array $data = []): void
    {
        PushNotification::create([
            'institution_id' => $user->institution_id ?? 1,
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }
}
