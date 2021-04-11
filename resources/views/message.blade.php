@if(!is_null($messages))
    @foreach($messages as $message)
        <div class="mt-2 message {{$ml}}" id="message{{$message->id}}">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center flex-wrap">
                        <h5 class="mr-4 text-break">{{$message->user->email}}</h5>
                        <span class="text-break">{{$message->created_at->format('d.m.Y h:i')}}</span>
                    </div>
                </div>

                <div class="card-body text-break">
                    {!! $message->message !!}
                </div>
                <div class="card-footer">
                    @if($message->replies->count() == 0 && $message->user_id == Auth()->id())
                        <a href="#" class="mt-1 edit col-md-2" data-id="{{$message->id}}">Редактировать</a>
                    @endif
                    <a href="#" class="mt-1 reply" data-id="{{$message->id}}">Ответить</a>
                </div>
            </div>
            @include('message', ['messages' => $message->replies, 'ml' => 'ml-4'])
        </div>
    @endforeach
@endif
