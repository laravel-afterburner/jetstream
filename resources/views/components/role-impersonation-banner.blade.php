@if(\App\Support\RoleImpersonation::isActive())
    @php
        $impersonatedRole = \App\Models\Role::find(\App\Support\RoleImpersonation::roleId());
        $impersonatedTeam = \App\Support\RoleImpersonation::teamId()
            ? \App\Models\Team::find(\App\Support\RoleImpersonation::teamId())
            : null;
    @endphp

    @if($impersonatedRole)
        <div class="bg-violet-600 text-white">
            <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="w-0 flex-1 flex items-center min-w-0">
                        <span class="flex p-2 rounded-lg bg-violet-700">
                            <svg class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </span>
                        <p class="ms-3 font-medium text-sm text-white">
                            Impersonating role: <strong>{{ $impersonatedRole->name }}</strong>
                            @if($impersonatedTeam)
                                on <strong>{{ $impersonatedTeam->name }}</strong>
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 sm:ms-3">
                        <form method="POST" action="{{ route('impersonate-role.stop') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white hover:text-violet-200 text-sm font-medium underline">
                                Stop Impersonating Role
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
