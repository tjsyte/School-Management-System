$(document).ready(function() {
    $('#gradesTable').DataTable({
        paging: true,
        searching: true,
        order: [
            [0, 'asc']
        ],
    });

    var table = $('#gradesTable').DataTable();

    $('#sectionFilter').on('change', function() {
        table.column(1).search(this.value).draw();
    });

    $('#yearFilter').on('change', function() {
        table.column(2).search(this.value).draw();
    });

    $('.grade-cell').each(function() {
        var grade = parseFloat($(this).text());
        if (grade >= 75 && grade <= 100) {
            $(this).addClass('grade-green');
        } else if (grade >= 0 && grade <= 74) {
            $(this).addClass('grade-red');
        }
    });

    $('.btn-edit').on('click', function() {
        var gradeId = $(this).data('id');
        var grade = $(this).data('grade');
        $('#grade_id').val(gradeId);
        $('#grade').val(grade);
    });

    $('.btn-delete').on('click', function() {
        var gradeId = $(this).data('id');
        if (confirm('Are you sure you want to delete this grade?')) {
            $.post('delete_grade.php', {
                grade_id: gradeId
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to delete grade.');
                }
            }, 'json');
        }
    });
});

document.getElementById('addSectionBtn').addEventListener('click', function() {
    let sectionFilter = document.getElementById('sectionFilter');
    let currentSections = Array.from(sectionFilter.options).map(option => option.value);

    let nextSection = String.fromCharCode(currentSections.length + 64);

    if (!currentSections.includes(nextSection)) {
        let newOption = document.createElement('option');
        newOption.value = nextSection;
        newOption.textContent = nextSection;
        sectionFilter.appendChild(newOption);
    }
});