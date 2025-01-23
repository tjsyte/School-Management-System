$(document).ready(function() {
    $('#studentsTable').DataTable({
        paging: true,
        searching: true,
        order: [
            [0, 'asc']
        ],
    });

    var table = $('#studentsTable').DataTable();

    $('#sectionFilter').on('change', function() {
        table.column(2).search(this.value).draw();
    });

    $('#yearFilter').on('change', function() {
        table.column(3).search(this.value).draw();
    });

    $('#gradeModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var studentId = button.data('student-id');
        var fullName = button.data('full-name');
        var studentYear = button.data('student-year');

        var modal = $(this);
        modal.find('#modalStudentId').val(studentId);
        modal.find('#modalStudentName').val(fullName);

        $.ajax({
            url: 'fetch_subject.php',
            type: 'POST',
            data: {
                year: studentYear
            },
            dataType: 'json',
            success: function(response) {
                var subjectSelect = modal.find('#subjectId');
                subjectSelect.empty();
                if (Array.isArray(response) && response.length > 0) {
                    response.forEach(function(subject) {
                        var option = $('<option></option>')
                            .attr('value', subject.SUBJECT_ID)
                            .text(subject.SUBJECT_NAME);
                        subjectSelect.append(option);
                    });
                } else {
                    var noOption = $('<option></option>')
                        .attr('value', '')
                        .attr('disabled', true)
                        .attr('selected', true)
                        .text('No subjects available');
                    subjectSelect.append(noOption);
                }
            },
            error: function() {
                console.error('Failed to fetch subjects.');
            }
        });
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