@extends('layouts.app')

@section('content')
<div class='container'>
    <div style='width: 100%;text-align: center'>
        Choose what you want !
    </div>
    <div class='row'>
        {!! Form::open() !!}
        <div>
            {!! Form::label('Type:') !!}
            {!! Form::select('type', ['0' => 'public', '1' => 'private'], null, ['class' => 'form-control']) !!}
        </div>
    
        <div>
            {!! Form::label('Status:') !!}
            {!! Form::select('status', $gameTypes, null, ['class' => 'form-control']) !!}
        </div>
        
        <br>
            {!! Form::submit('Start', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}    
        
    </div>
</div>
@endsection


