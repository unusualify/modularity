@extends("{$BASE_KEY}::layouts.master")

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! unusualConfig('name') !!}
    </p>
@endsection
