<?php

namespace App\Support;

use App\Models\CommunityMember;
use App\Models\User;
use App\Notifications\UserMentioned;

class Mentions
{
    public static function notify(string $text, $activity, $actor): void
    {
        preg_match_all('/@([\w.\-]+(?:\s[\w.\-]+)?)/u', $text, $matches);

        if (empty($matches[1])) {
            return;
        }

        $teamId = $activity->community_id ?? null;
        if (!$teamId) {
            return;
        }

        $memberUserIds = CommunityMember::where('community_id', $teamId)
            ->where('status', 'active')
            ->pluck('user_id');

        $users = User::whereIn('id', $memberUserIds)->get();

        foreach ($matches[1] as $handle) {
            $handle = trim($handle);

            $mentioned = $users->first(function ($u) use ($handle) {
                return strcasecmp($u->name, $handle) === 0
                    || strcasecmp(strtok($u->name, ' '), $handle) === 0
                    || strcasecmp(explode('@', $u->email)[0], $handle) === 0;
            });

            if ($mentioned && $mentioned->id !== $actor->id) {
                $mentioned->notify(new UserMentioned($activity, $actor));
            }
        }
    }
}