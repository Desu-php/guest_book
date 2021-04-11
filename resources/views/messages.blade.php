@extends('layouts.app')
@section('content')
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-center row ">
            <div class="d-flex flex-column col-md-12">
                <div class="bg-white p-2 px-4">
                    <div class="mt-4 mb-4 parent">
                        <div contenteditable="true" class="border border-dark w-100 mr-3 custom-textarea p-3"></div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-primary mt-2 send-message" type="button">
                                Comment
                                <div class="spinner-border" style="width: 1rem;height: 1rem; display: none"
                                     role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </button>
                            <div class="mt-2 ml-4 d-flex align-items-center">
                                <a href="#" class="upload_image">Загрузить картинку</a>
                                <input type="file" class="input-upload" name="image" accept="image/*" hidden>
                                <div class="spinner-border" role="status" style="display: none">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="messages">
                        @include('message', ['messages' => $messages, 'ml' => ''])
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            {{$messages->links()}}
        </div>

    </div>
    <div class="modal" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">Ошибка!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Ок</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            var body = $('body')

            function showModal(title, body) {
                var myModal = $('#myModal')
                myModal.find('#title').text(title)
                myModal.find('#modal-body').html(body)
                myModal.modal('show')
            }

            body.on('click', '.upload_image', function (e) {
                e.preventDefault()
                $(this).next().click()
            })

            body.on('change', '.input-upload', function (e) {
                var input = $(this)
                input.prev().hide()
                input.next().show()
                var formData = new FormData()
                formData.set('image', this.files[0])
                formData.set('_token', "{{csrf_token()}}")

                $.ajax({
                    url: "{{route('messages.upload')}}",
                    type: "POST",
                    processData: false,
                    contentType: false,
                    dataType: 'JSON',
                    data: formData,
                    success: function (data) {
                        if (!data.success) {
                            showModal('Ошибка!', '<div class="alert alert-danger" role="alert">Что-то пошло не так!</div>')
                        }
                        input.parents('.parent')
                            .find('.custom-textarea')
                            .append(`<img class="img-fluid d-block" src="${data.url}" alt="image" >`)
                    },
                    error: function (data) {
                        error(data)
                    },
                    complete: function () {
                        input.prev().show()
                        input.next().hide()
                    }
                })
            })

            function error(data) {
                var status = data.status
                if (status === 400) {
                    var errors = data.responseJSON.errors,
                        html = ''
                    for (var key in errors) {
                        errors[key].forEach(function (value) {
                            html += `<div class="alert alert-danger" role="alert">${value}</div>`
                        })
                    }
                    showModal('Ошибка!', html)
                } else if (status === 401) {
                    showModal('Авторизуйтесь', `<div class="alert alert-danger" role="alert">${data.responseJSON.message}</div>`)
                } else if (status === 419) {

                } else if (status === 404) {
                    showModal('Ошибка!', `<div class="alert alert-danger" role="alert">${data.responseJSON.message}</div>`)
                } else if (status === 403){
                    showModal('Ошибка!', `<div class="alert alert-danger" role="alert">${data.responseJSON.message}</div>`)
                }
            }

            body.on('click', '.send-message', function () {
                var textArea = $(this).parents('.parent').find('.custom-textarea')
                var btn = $(this)
                var reply_id = null
                if (textArea.attr('data-message-id')){
                    reply_id = textArea.attr('data-message-id')
                    $(`#message${reply_id} .edit`).remove()
                }

                btn.attr('disabled', true)
                btn.find('div').show()
                $.ajax({
                    url: "{{route('messages.store')}}",
                    type: "POST",
                    data: ({"_token": "{{csrf_token()}}", "message": textArea.html(), "reply": reply_id}),
                    success: function (data) {
                        if (!data.success) {
                            showModal('Ошибка!', '<div class="alert alert-danger" role="alert">Что-то пошло не так!</div>')
                        }
                        textArea.html("")
                        $('.clone').remove()
                        var message = data.message,
                            html = `
                             <div class="mt-2 ${message.reply_id !== null ? "ml-4" : ""}" id="message${message.id}">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center row">
                                                <h5 class="mr-4 text-break">${message.user.email}</h5>
                                                <span class="text-break">${message.date}</span>
                                            </div>
                                        </div>

                                        <div class="card-body text-break">
                                           ${message.message}
                        </div>
                        <div class="reply-section card-footer">
                             <a href="#" class="mt-1 edit col-md-2" data-id="${message.id}">Редактировать</a>
                            <a href="#" class="mt-1 reply" data-id="${message.id}">Ответить</a>
                                        </div>
                                    </div>
                                </div>
                        `
                        if (message.reply_id !== null) {
                            $(`#message${message.reply_id}`).append(html)
                        } else {
                            $('#messages').prepend(html)
                        }

                    },
                    error: function (data) {
                        error(data)
                    },
                    complete: function () {
                        btn.removeAttr('disabled')
                        btn.find('div').hide()
                    }
                })

            })

            function cloneCommentBox(clicked) {
                $('.clone').remove()
                var commentBox = $('.parent').clone()
                commentBox.addClass('clone')
                commentBox.find('.custom-textarea').html("").attr('data-message-id', clicked.attr('data-id'))
                clicked.closest('.card').after(commentBox)
                commentBox.find('.custom-textarea').focus()
                return commentBox
            }

            body.on('click', '.reply', function (e) {
                e.preventDefault()
                cloneCommentBox($(this))
            })

            body.on('click', '.edit', function (e) {
                e.preventDefault()
                var textEditor = cloneCommentBox($(this))
                textEditor.find('.custom-textarea').html($(this).closest('.card').find('.card-body').html())
                textEditor.find('.send-message').removeClass('send-message').addClass('edit-message')
            })

            body.on('click', '.edit-message', function () {
                var textArea = $(this).parents('.parent').find('.custom-textarea')
                var btn = $(this)
                btn.attr('disabled', true)
                btn.find('div').show()
                $.ajax({
                    url: "{{route('messages.update')}}",
                    type: "PUT",
                    data: ({
                        "_token": "{{csrf_token()}}",
                        "message": textArea.html(),
                        "id": textArea.data('message-id')
                    }),
                    success: function (data) {
                        if (!data.success) {
                            showModal('Ошибка!', '<div class="alert alert-danger" role="alert">Что-то пошло не так!</div>')
                        }
                        $('.clone').remove()
                        $(`#message${textArea.data('message-id')}`)
                            .find('.card-body')
                            .html(data.message)
                    },
                    error: function (data) {
                        error(data)
                    },
                    complete: function () {
                        btn.removeAttr('disabled')
                        btn.find('div').hide()
                    }
                })
            })
        });

    </script>
@endsection
