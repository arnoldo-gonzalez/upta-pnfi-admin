<table>
    <thead>
        <tr>
            <th>{{ __('Project') }}</th>
            <th>{{ __('Section') }}</th>
            <th>{{ __('Students') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $project)
            <tr>
                <td>
                    <strong>{{ $project->title }}</strong><br>
                    <span style="font-size: 10px; color: #666;">{{ $project->investigationLine->name }}</span>
                </td>
                <td>{{ $project->section->name }} - {{ $project->section->trayecto->name }}<br>({{ $project->section->period }})</td>
                <td>
                    <ul style="margin: 0; padding-left: 15px;">
                        @foreach($project->students as $student)
                            <li>{{ $student->enrollment->student->name }}</li>
                        @endforeach
                    </ul>
                </td>
                <td>{{ __($project->status->name ?? 'N/A') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>