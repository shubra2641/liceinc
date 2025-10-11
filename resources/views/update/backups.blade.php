@extends('layouts.admin')

@section('title', trans('app.Updater Backups'))

@section('admin-content')
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>{{ trans('app.Updater Backups') }}</h3>
        </div>
        <div class="admin-card-body">
            <p>{{ trans('app.backups_list_help') }}</p>
            @if(empty($files))
                <div class="text-muted">{{ trans('app.no_backups_found') }}</div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ trans('app.File') }}</th>
                            <th>{{ trans('app.Size') }}</th>
                            <th>{{ trans('app.Modified') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td>{{ $file['name'] }}</td>
                            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                            <td>{{ $file['mtime'] }}</td>
                            <td>
                                <form method="POST" action="{{ route('backups.restore') }}" class="restore-form" data-confirm="{{ e(trans('app.confirm_restore_backup')) }}">
                                    @csrf
                                    <input type="hidden" name="backup" value="{{ $file['name'] }}" />
                                    <button class="btn btn-danger">{{ trans('app.Restore') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection

