<div id="searchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('searchModal')">&times;</span>
        <h2>Search Student</h2>
        <form id="searchForm">
            <label for="searchQuery">Enter ID Number:</label>
            <input type="text" id="searchQuery" placeholder="Enter ID Number" required>
            <button type="button" onclick="searchStudent()">Search</button>
        </form>
    </div>
</div>

<div id="sitInModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('sitInModal')">&times;</span>
        <h2>Sit-in Form</h2>
        <form id="sitInForm" action="a-currents.php" method="post">
            <label>ID Number:</label>
            <input type="text" id="idno" name="idno" readonly>
            <label>Student Name:</label>
            <input type="text" id="studentName" readonly>
            <label>Purpose:</label>
            <select name="purpose" required>
                <option>C Programming</option>
                <option>Python</option>
                <option>ASP .net</option>
                <option>Java</option>
            </select>
            <label>Lab:</label>
            <select name="lab" required>
                <option value="530">530</option>
                <option value="524">524</option>
                <option value="526">526</option>
                <option value="542">542</option>
                <option value="540">540</option>
            </select>
            <button type="submit" name="approve_sit_in">Approve</button>
            <button type="button" onclick="closeModal('sitInModal')">Disapprove</button>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'block';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function searchStudent() {
        const id = document.getElementById('searchQuery').value;
        if (id) {
            fetch(`fetch_student.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById('idno').value = data.idno;
                        document.getElementById('studentName').value = `${data.firstname} ${data.lastname}`;
                        closeModal('searchModal');
                        openModal('sitInModal');
                    }
                })
                .catch(error => {
                    alert('An error occurred while fetching student details.');
                    console.error(error);
                });
        } else {
            alert('Please enter an ID number!');
        }
    }
</script>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 30%;
        border-radius: 8px;
    }

    .close {
        float: right;
        font-size: 28px;
        cursor: pointer;
    }

    .modal h2 {
        text-align: center;
        margin-bottom: 20px;
        background-color: #4d5572;
        color: white;
        padding: 10px;
        border-radius: 5px;
    }

    .modal label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
    }

    .modal input, .modal select, .modal button {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .modal button {
        background-color: #4d5572;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 20px;
    }

    .modal button:hover {
        background-color: #3a4256;
    }
</style>
