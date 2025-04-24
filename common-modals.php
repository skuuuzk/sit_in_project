
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
        margin: 10% auto;
        padding: 20px;
        border: 2px solid #475E53;
        width: 30%;
        border-radius: 8px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Soft shadow */
        background-color:#BACEAB;
        color:rgb(11, 27, 3);
    }

    .close {
        float: right;
        font-size: 28px;
        cursor: pointer;
        padding: 10px;
        color: white;
    }

    .modal h2 {
        text-align: center;
        margin-bottom: 20px;
        background-color: #475E53;
        color: white;
        padding: 10px;
        border-radius: 5px;
    }

    .modal label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
    }

    .modal input, .modal select, .modal textarea {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .buttons { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 15px; 
        }
        .save-btn, .cancel-btn { 
            padding: 10px 15px; 
            border: none; 
            cursor: pointer; 
            flex: 1;
            border-radius: 5px; 
            font-size: 14px; 
        }
        .save-btn { 
            background-color: #475E53; 
            color: white; 
            margin-right: 5px; 
        }
        .cancel-btn { 
            background-color: #9AAE97; 
            color:rgb(31, 44, 37); 
            margin-left: 5px; 
        }
        .save-btn:hover { 
            background-color: #DEE9DC; 
            color: seagreen;  
        }
        .cancel-btn:hover { 
            background-color: #DEE9DC; 
            color: seagreen;  
        }
</style>


<div id="searchModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('searchModal')">&times;</span>
        <h2>Search Student</h2>
        <form id="searchForm" style="display: flex; align-items: center; gap: 10px;">
            <input type="text" id="searchQuery" placeholder="Enter ID Number" required>
            <button type="button" class="save-btn" onclick="searchStudent()">Search</button>
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
            
            <div class="buttons">
                    <button type="submit" class="save-btn"  name="approve_sit_in">Approve</button>
                    <button type="button" class="cancel-btn" onclick="closeModal('sitInModal')">Disapprove</button>
            </div>
        </form>
    </div>
</div>

<div id="feedbackModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('feedbackModal')">&times;</span>
        <h2>Submit Feedback</h2>
        <form id="feedbackForm">
            <label for="feedbackText">Your Feedback:</label>
            <textarea id="feedbackText" name="feedbackText" rows="4" required></textarea>
            <div class="buttons">
                <button type="button" class="save-btn" onclick="submitFeedback()">Submit</button>
                <button type="button" class="cancel-btn" onclick="closeModal('feedbackModal')">Cancel</button>
            </div>
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

    function submitFeedback() {
        const feedbackText = document.getElementById('feedbackText').value;
        if (feedbackText) {
            fetch('submit_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ feedback: feedbackText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Feedback submitted');
                    closeModal('feedbackModal');
                } else {
                    alert('Failed to submit feedback');
                }
            })
            .catch(error => {
                alert('An error occurred while submitting feedback.');
                console.error(error);
            });
        } else {
            alert('Please enter your feedback!');
        }
    }
</script>