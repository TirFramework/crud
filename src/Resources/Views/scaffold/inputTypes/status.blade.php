@php
$values =  ['published'=>trans('crud::panel.published'), 'unpublished'=>trans('crud::panel.unpublished'),'draft'=> trans('crud::panel.draft')];
@endphp

<div class="{{ $field->col ?? 'col-12 col-md-12' }}">
    <div class="form-group">
        {{-- get config from config file with config() function --}}
        {!! Form::select('status',$values,null,['class' => 'form-control']) !!}
        {!! Form::label('status', trans('crud::panel.status'), ['class' => 'control-label']) !!}
    </div>
</div>
 