@if(session('impersonating'))
    @php
        $impersonatedUser = \App\Models\User::find(session('impersonated_user_id'));
        $originalUser = \App\Models\User::find(session('impersonator_id'));
    @endphp
    
    @if($impersonatedUser && $originalUser)
        <div class="bg-orange-500 text-white">
            <div class="max-w-screen-xl mx-auto py-2 px-3 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between flex-wrap">
                    <div class="w-0 flex-1 flex items-center min-w-0">
                        <span class="flex p-2 rounded-lg bg-orange-600">
                            <svg class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <p class="ms-3 font-medium text-sm text-white">
                            Impersonating: <strong>{{ $impersonatedUser->name }}</strong> ({{ $impersonatedUser->email }})
                        </p>
                    </div>
                    <div class="shrink-0 sm:ms-3">
                        <form method="POST" action="{{ route('impersonate.stop') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white hover:text-orange-200 text-sm font-medium underline">
                                Stop Impersonating
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

