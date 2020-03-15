{{-- first we check access then check which page create or edit
not isset for check which page edit or create --}}
@if(\App\CustomEnhancement\Authorization\Acl::checkAccess($crud->name, $field->name))

    <div class="form-group">
        {!! Form::label('status', trans('panel.status'), ['class' => 'col-md-2 control-label']) !!}
        <div class="col-md-10">
            {{-- get config from config file with config() function --}}
            {!! Form::select('status',['new'=>trans('panel.new'),'doing'=>trans('panel.doing'),'rejected'=>trans('panel.rejected'), 'approved'=>trans('panel.approved')],null,['class' => 'form-control input-lg select2']) !!}
        </div>
    </div>
@else
    @php $value = null;
       if(isset($item->status)){
       $value = trans('panel.'.$item->status);
        }

    $class ='bg-warning';
     if(isset($item->status)){
         if($item->status == 'rejected')
              $class = 'bg-danger';
         elseif ($item->status == 'approved'){
              $class ='bg-success';
         }elseif ($item->status == 'doing'){
             $class ='bg-info';
         }
    }
    @endphp
    @if(isset($item))
    <div class="form-group">
        {!! Form::label($field->name, trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
        <div class="col-md-10">
            <div class="form-control {{$class}}" style="padding-top: 7px">
                {{$value}}
            </div>
        </div>
    </div>
    @endif
@endif

