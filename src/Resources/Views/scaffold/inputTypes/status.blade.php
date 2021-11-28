@php
$values =  ['published'=>trans('core::panel.published'), 'unpublished'=>trans('core::panel.unpublished'),'draft'=> trans('core::panel.draft')];
@endphp

<div class="{{ $field->col ?? 'col-12 col-md-12' }}">
    <div class="form-group">
        {{-- get config from config file with config() function --}}
        {!! Form::select('status',$values,null,['class' => 'form-control']) !!}
        {!! Form::label('status', trans('core::panel.status'), ['class' => 'control-label']) !!}
    </div>
</div>
 