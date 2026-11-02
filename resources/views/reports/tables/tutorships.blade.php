<table>
    <thead>
        <tr>
            <th>{{ __('Project') }}</th>
            <th>{{ __('Section') }}</th>
            <th>{{ __('Tutor Assigned') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $project)
            <tr>
                <td>
                    <strong>{{ $project->title }}</strong><br>
                    <span style="font-size: 10px; color: #666;">{{ $project->code ?? 'N/A' }}</span>
                </td>
                <td>{{ $project->section->name }} - {{ $project->section->trayecto->name }}<br>({{ $project->section->period }})</td>
                <td>{{ $project->tutor->name ?? 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>