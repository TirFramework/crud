{{--Submit & Cancel--}}
<div class="form-group">
    {!! Form::label('', '', ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!!  Form::submit(trans('crud::panel.create_close'),['name'=>'save_close','class'=>'btn btn-lg btn-success save_close'])!!}
        {!!  Form::submit(trans('crud::panel.create'),['class'=>'btn btn-lg btn-info save'])!!}
            <a type="button" href="{{route("$crud->name.index")}}" class="btn btn-lg btn-warning">{{trans('crud::panel.cancel')}}</a>
    </div>
</div>
