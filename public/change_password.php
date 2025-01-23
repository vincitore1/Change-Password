<!DOCTYPE html>
<html>
<head>
    <title>Ganti Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: none;
        }
        .alert-success {
            background-color: #dff0d8;
            border-color: #d6e9c6;
            color: #3c763d;
        }
        .alert-danger {
            background-color: #f2dede;
            border-color: #ebccd1;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="alert" id="alert"></div>
    <form id="formGantiPassword">
        <div class="form-group">
            <label>Password Saat Ini:</label>
            <input type="password" name="password_lama" required>
        </div>
        <div class="form-group">
            <label>Password Baru:</label>
            <input type="password" name="password_baru" required>
        </div>
        <div class="form-group">
            <label>Konfirmasi Password Baru:</label>
            <input type="password" name="konfirmasi_password" required>
        </div>
        <button type="submit">Ganti Password</button>
    </form>

    <script>
        document.getElementById('formGantiPassword').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alert = document.getElementById('alert');
                alert.style.display = 'block';
                alert.textContent = data.pesan;
                alert.className = 'alert ' + (data.status ? 'alert-success' : 'alert-danger');
                
                if(data.status) {
                    document.getElementById('formGantiPassword').reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>