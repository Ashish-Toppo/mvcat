<?php

namespace App\Helpers;

final class EventPerm
{
    // Basic
    public const EDIT_EVENT_DETAILS         = 1 << 0;       // 2^0
    public const EDIT_EVENT_FILE_CONTENT    = 1 << 1;       // 2^1
    public const VIEW_PARTICIPANTS          = 1 << 2;       // 2^2
    public const CHECKIN_PARTICIPANTS       = 1 << 3;       // 2^3

    // Advanced
    public const PUBLISH_EVENT              = 1 << 4;       // 2^4
    public const DELETE_EVENT               = 1 << 5;       // 2^5

    // anlytics
    public const VIEW_ANALYTICS             = 1 << 6;       // 2^6
    public const GENERATE_REPORTS           = 1 << 7;       // 2^7

    public const ISSUE_CERTIFICATES         = 1 << 8;       // 2^8
    public const EDIT_TEMPLATES             = 1 << 9;       // 2^9

    // All permissions (owner)
    public const ALL = 0xFFFFFFFF; // or compute OR of all bits

    public static function fromArray(array $permData): int
    {
        $map = [
            'edit'           => self::EDIT_EVENT_DETAILS,
            'files'          => self::EDIT_EVENT_FILE_CONTENT,
            'registrations'  => self::VIEW_PARTICIPANTS,
            'checkin'        => self::CHECKIN_PARTICIPANTS,
            'publish'        => self::PUBLISH_EVENT,
            'delete'         => self::DELETE_EVENT,
            'analytics'      => self::VIEW_ANALYTICS,
            'reports'        => self::GENERATE_REPORTS,
            'certificates'   => self::ISSUE_CERTIFICATES,
            'templates'      => self::EDIT_TEMPLATES,
        ];

        $bitmask = 0;
        foreach ($map as $key => $bit) {
            if (!empty($permData[$key])) $bitmask |= $bit;
        }

        return $bitmask;
    }
}
