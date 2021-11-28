{{-- @php
    $placeholder = null;
    if(isset($field->placeholder)){
        $placeholder = $field->placeholder;
    }
    $option = null;
    if(isset($field->option)){
        $option = $field->option;
    }
@endphp

<div class="{{$field->col ?? 'col-12'}}">
    <div class="form-group">
        {!! Form::textarea($field->name,null,['class' => 'form-control','placeholder'=> $placeholder , $option=> $option, 'rows'=>'2'])!!}
        {!! Form::label($field->name, trans("$crud->name::panel.$field->display"), ['class' => 'control-label']) !!}
    </div>
</div>
 --}}

<x-field :field="$field" :item="$model" :message="$message ?? null" >
	{!!  Form::textarea( $field->name, null, ['rows'=>'2', $field->option ?? null] )  !!}
</x-field>

