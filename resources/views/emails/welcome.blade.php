@component('mail::message')

<b>Добро пожаловать {{$mail_body->first_name}}!</b>
<p>Вас приветствует {{$mail_body->school_name}}</p>


<p>Для активации Вашего акааунта просим перейти по данной <a href="{{$mail_body->activation_url}}">ссылке</a></p>

@endcomponent