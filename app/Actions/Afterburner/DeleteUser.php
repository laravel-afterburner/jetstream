<?php

namespace App\Actions\Afterburner;

use App\Actions\Afterburner\DeleteTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUser
{
    /**
     * Create a new action instance.
     */
    public function __construct(protected DeleteTeam $deletesTeams)
    {
    }

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }

    /**
     * Delete the teams and team associations attached to the user.
     */
    protected function deleteTeams(User $user): void
    {
        $user->teams()->detach();

        $user->ownedTeams->each(function (Team $team) {
            $this->deletesTeams->delete($team);
        });
    }
}
