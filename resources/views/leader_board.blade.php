<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
<style>
    .loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Adjust opacity as needed */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999; /* Ensure it appears on top */
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<div class="container">
    <h1 style="text-align: center">Leaderboard</h1>
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>Remove</th>
            <th>Name</th>
            <th>Add Points</th>
            <th>Sub Points</th>
            <th>Total Points</th>
        </tr>
        </thead>
        <tbody id="leaderboard-table">
        </tbody>
    </table>

</div>

<div class="modal fade" id="leaderModal" tabindex="-1" aria-labelledby="leaderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaderModalLabel" style="text-align: center">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered">
                    <tr><td>Name: </td> <td id="leaderName"></td></tr>
                    <tr><td>Age: </td> <td id="leaderAge"></td></tr>
                    <tr><td>Points: </td> <td id="leaderPoints"></td></tr>
                    <tr><td>Address: </td> <td id="leaderAddress"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="loader" class="loader" style="display: none;">
    <div class="d-flex justify-content-center">
        <div class="spinner-border" role="status">
            <span class="sr-only">Removing...</span>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
<script>
    // Fetch leaderboard data on page load
    axios.get('http://laravel-leader-board.test/api/leaderboard')
        .then(response => {
            const leaderboardData = response.data;
            // console.log(leaderboardData)
            // Create table rows dynamically
            for (const [key, leaderboard] of Object.entries(leaderboardData)) {
            // leaderboardData.forEach(leaderboard => {
                const row = document.createElement('tr');
                row.innerHTML = `
                        <td style="text-align: center;"><button class="btn btn-danger remove-button" data-id=${leaderboard.id} onclick="remove_button_clicked(${leaderboard.id})">X</button></td>
                        <td><button class="btn btn-link" data-id=${leaderboard.id} onclick="details_button_clicked(${leaderboard.id})">${leaderboard.name}</button></td>
                        <td style="text-align: center;"><button class="btn btn-success increment" data-id=${leaderboard.id} onclick="increment_button_clicked(${leaderboard.id})">+</button></td>
                        <td style="text-align: center;"><button class="btn btn-warning decrement" data-id=${leaderboard.id} onclick="decrement_button_clicked(${leaderboard.id})">-</button></td>
                        <td>${leaderboard.points}</td>
                    `;
                document.getElementById('leaderboard-table').appendChild(row);
            }
        })
        .catch(error => {
            console.error('Error fetching leaderboard data:', error);
        });

    // Function to call remove
    function remove_button_clicked(id) {
        // Show the loader
        const loader = document.getElementById('loader');
        loader.style.display = 'flex';
        // console.log("Clicked: "+id);

        // Calling the remove API
        const remove_url = 'http://laravel-leader-board.test/api/leader/remove/'+id
        console.log("calling url: "+remove_url)
        axios.delete(`http://laravel-leader-board.test/api/leader/remove/${id}`)
            .then(() => {
                // Hide loader and show success flash message
                loader.style.display = 'none';
                alert('Leaderboard entry removed successfully!');
                location.reload();
            })
            .catch(error => {
                // Hide loader and show error flash message
                loader.style.display = 'none';
                alert('Error removing entry: ' + error.message);
            });
    }

    // Function to call increment points
    function increment_button_clicked(id) {
        // Show the loader
        const loader = document.getElementById('loader');
        // loader.style.display = 'flex';
        // console.log("Increment Clicked: "+id);

        // Calling the increment API
        axios.post('/api/leader/point', {
            id: id,
            mode: 'increment'
        })
        .then(() => {
            // Hide loader and show success flash message
            loader.style.display = 'none';
            // alert('Point incremented successfully!');
            location.reload();
        })
        .catch(error => {
            // Hide loader and show error flash message
            loader.style.display = 'none';
            alert('Error on point increment: ' + error.message);
        });
    }

    // Function to call decrement points
    function decrement_button_clicked(id) {
        // Show the loader
        const loader = document.getElementById('loader');
        loader.style.display = 'flex';
        // console.log("Decrement Clicked: "+id);

        // Calling the increment API
        axios.post('/api/leader/point', {
            id: id,
            mode: 'decrement'
        })
            .then(() => {
                // Hide loader and show success flash message
                loader.style.display = 'none';
                // alert('Point incremented successfully!');
                location.reload();
            })
            .catch(error => {
                // Hide loader and show error flash message
                loader.style.display = 'none';
                alert('Error on point increment: ' + error.message);
            });
    }

    // Function to call details
    function details_button_clicked(id) {
        // Show the loader
        const loader = document.getElementById('loader');
        const modal = new bootstrap.Modal(document.getElementById('leaderModal'));
        // loader.style.display = 'flex';
        console.log("details Clicked: "+id);
        const url = "/api/leader/show/"+id
        axios.get(url)
            .then(response => {
                const leaderboardData = response.data;
                for (const [key, leaderboard] of Object.entries(leaderboardData.data)) {
                    console.log("key: " + key)
                    console.log('data: ' + leaderboard)
                    // Populate the modal content with the leaderboard data
                    if (key === 'name'){
                        document.getElementById('leaderName').textContent = leaderboard;
                    }

                    if (key === 'age'){
                        document.getElementById('leaderAge').textContent = leaderboard;
                    }

                    if (key === 'points'){
                        document.getElementById('leaderPoints').textContent = leaderboard;
                    }

                    if (key === 'address'){
                        document.getElementById('leaderAddress').textContent = leaderboard;
                    }
                }
                // Hide loader and show error flash message
                // loader.style.display = 'none';
                // Show the modal
                modal.show();
            })
            .catch(error => {
                // Hide loader and show error flash message
                loader.style.display = 'none';
                console.error(error);
                // Handle API errors here
            });


    }

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
