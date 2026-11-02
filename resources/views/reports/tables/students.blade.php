@use('App\Enums\UserStatus')

<table>
    <thead>
        <tr>
            <th>{{ __('ID Number') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Section') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $student)
            <tr>
                <td>{{ $student->credential }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td>
                    @php
                        $enrollment = $student->enrollments->first();
                    @endphp
                    @if($enrollment && $enrollment->section)
                        {{ $enrollment->section->name }} - {{ $enrollment->section->trayecto->name }}
                    @else
                        N/A
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($student->status_id === UserStatus::PENDING->value)
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ __('Pending') }}
                        </span>
                    @elseif($student->status_id === UserStatus::ACTIVE->value)
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            {{ __('Active') }}
                        </span>
                    @else
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            {{ __('Rejected') }}
                        </span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>