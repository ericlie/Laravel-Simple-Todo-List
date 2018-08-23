@extends('layouts.app')

@section('content')
<div class="container">
    <div class="btn btn-primary update">update</div>
    <table class="table table-hover table-bordered" id="taskTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Body</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

@endsection
@push('js')
<script>
    $(function () {
        const { log, error } = console
        const findRowById = function (id) {
            return $('#taskTable tbody').find(`tr[data-id="${id}"]`)
        }
        $('body')
        .on('click', 'a.delete', function (ev) {
            alert(`clicked id: ${$(this).attr('data-id')}`)
            findRowById($(this).attr('data-id')).remove()
        })
        .on('click', '.update', function (ev) {
            const id = 40;
            const $row = findRowById(id)
            const cell = $row.find('td').get(1) // <td> ini text nya </td>
            const $cell = $(cell)
            // cell.innerHTML = 'cob acoba'
            $cell.text('coba pakai jquery')
            // log(cell);
        })
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            url: '{{ route('tasks.all') }}',
            method: 'GET',
            dataType: 'JSON',
            success: function (tasks) {
                let route = '{{ route('tasks.destroy', ['task' => 'evan']) }}'
                let template = ({id, task, user, delete_route}) => {
                    return `
                        <tr data-id="${id}">
                            <td># ${id} - By ${user.name} </td>
                            <td data-id="${id}">${task}</td>
                            <td><a data-id="${id}" data-url="${delete_route}" class="btn btn-danger delete">HAPUS</a></td>
                        </tr>
                    `
                }
                let final = tasks.map(template).join('')
                $('#taskTable tbody').append($(final))
            },
            error: function ({ responseJSON: { errors, message }}) {
                error(errors)
                console.log(message);
                alert(message)
            }
        })
    })
</script>
@endpush