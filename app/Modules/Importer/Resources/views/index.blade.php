@extends('importer::layouts.master')

@section('content')
    <div class="card first">
        <form method="POST" action="{{ route('importer.store') }}" enctype="multipart/form-data">
            <div class="form-field">
                <header>{{ __('Dołącz plik') }}</header>
                <input id="html_file" type="file" name="html_file" hidden>
                <label for="html_file">{{ __('Dołącz...') }}</label>
                @error('html_file')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="button-wrapper">
                <button type="submit">{{ __('Wyodrębnij dane') }}</button>
            </div>
        </form>
    </div>
    <div class="card second">
        <h2>{{ __('Zaimportowane pliki') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>
                        {{ __('Data') }}
                    </th>
                    <th>
                        {{ __('Przetworzone pozycje') }}
                    </th>
                    <th>
                        {{ __('Utworzone nowe pozycje') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $import)
                    <tr>
                        <td>
                            {{ $import->run_at }}
                        </td>
                        <td>
                            {{ $import->entries_processed }}
                        </td>
                        <td>
                            {{ $import->entries_created }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        const htmlFile = document.getElementById('html_file');

        htmlFile.addEventListener('change', function() {
            const fileName = document.getElementById('html_file').files[0].name;
            const nextSibling = this.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    </script>
@endsection
