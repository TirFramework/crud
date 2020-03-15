{{--Submit & Cancel--}}
    <div class="form-group">
        {!! Form::label('', '', ['class' => 'col-md-2 control-label']) !!}
        <div class="col-md-10">
            {!!  Form::submit(trans('crud::panel.update_close'),['name'=>'save_close','class'=>'btn btn-lg btn-success save_close'])!!}
            {!!  Form::submit(trans('crud::panel.update'),['class'=>'btn btn-lg btn-info save'])!!}
            {{-- @if(App\Modules\Authorization\Acl::checkAccess($crud->name, 'index')) --}}
                <a type="button" href="{{route("$crud->name.index")}}" class="btn btn-lg btn-warning">{{trans('crud::panel.cancel')}}</a>
            {{-- @else --}}
                {{-- <a type="button" href="{{route("dashboard")}}" class="btn btn-lg btn-warning">{{trans('crud::panel.cancel')}}</a> --}}
            {{-- @endif --}}
        </div>
    </div>



