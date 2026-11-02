@use('App\Enums\UserStatus')

<table>
    <thead>
        <tr>
            <th>{{ __('ID Number') }}</th>
            <th>{{ __('Teacher') }}</th>
            <th>{{ __('Contact') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $teacher)
            <tr>
                <td>{{ $teacher->credential }}</td>
                <td>
                    <strong>{{ $teacher->name }}</strong><br>
                    <span style="font-size: 10px; color: #666;">{{ $teacher->email }}</span>
                </td>
                <td>{{ $teacher->phone }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($teacher->status_id === UserStatus::PENDING->value)
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ __('Pending') }}
                        </span>
                    @elseif($teacher->status_id === UserStatus::ACTIVE->value)
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