<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <x-heading level="h1">{{ __('Tutoring Requests') }}</x-heading>

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="mb-4 md:mb-0">
                <x-heading level="h1" class="mb-0">{{ __('Tutoring Requests') }}</x-heading>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Current capacity:') }} <strong>{{ auth()->user()->max_tutored_projects - auth()->user()->availableTutorSlots() }} / {{ auth()->user()->max_tutored_projects }}</strong>
                </p>
            </div>
            
            <form action="{{ route('tutors.increaseCapacity') }}" method="POST">
                @csrf
                <button type="submit" class="bg-upta-50 text-upta-700 border border-upta-200 px-4 py-2 rounded-md text-sm font-semibold hover:bg-upta-100 transition">
                    {{ __('Increase My Capacity') }}
                </button>
            </form>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6">
            @forelse($requests as $project)
                <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6 flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-xl font-bold text-upta-900">{{ $project->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $project->section->name }} - {{ $project->section->trayecto->name }}</p>
                        
                        <div class="mt-3">
                            <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Team Members') }}</p>
                            <ul class="flex flex-wrap gap-2 mt-1">
                                @foreach($project->students as $student)
                                    <li class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $student->enrollment->student->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 w-full md:w-auto">
                        <form action="{{ route('tutors.reject', $project) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-md font-semibold hover:bg-red-100 transition">
                                {{ __('Reject') }}
                            </button>
                        </form>
                        <form action="{{ route('tutors.accept', $project) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-md font-semibold hover:bg-green-700 transition shadow-sm">
                                {{ __('Accept') }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-center bg-white p-12 rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500">{{ __('You have no pending tutoring requests.') }}</p>
                </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    </div>
</x-app-layout>