@php
$values =  ['published'=>trans('crud::panel.published'), 'unpublished'=>trans('crud::panel.unpublished'),'draft'=> trans('crud::panel.draft')];
@endphp
<div class="form-group">
    {!! Form::label('status', trans('crud::panel.status'), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {{-- get config from config file with config() function --}}
        {!! Form::select('status',$values,null,['class' => 'form-control']) !!}
    </div>
</div>
