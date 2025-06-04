<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSCERMS - Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f0f4f0;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Standard font sizes matching your system */
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 11px; }
        .text-base { font-size: 12px; }
        .text-lg { font-size: 14px; }
        .text-xl { font-size: 16px; }
        .text-2xl { font-size: 18px; }

        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .reset-container {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .reset-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .reset-form {
            padding: 3rem;
        }

        .reset-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .reset-subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 11px;
        }

        .user-info {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .user-info h4 {
            margin: 0 0 0.5rem 0;
            color: #1f2937;
            font-weight: 600;
            font-size: 12px;
        }

        .user-info p {
            margin: 0;
            color: #6b7280;
            font-size: 11px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 11px;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 12px;
            font-family: 'Poppins', sans-serif;
            line-height: 1.5;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #047857;
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.1);
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 78, 59, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #064e3b;
            text-decoration: none;
            font-weight: 500;
            font-size: 11px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #059669;
        }

        .error-text {
            color: #dc2626;
            font-size: 10px;
            margin-top: 0.5rem;
        }

        .help-text {
            color: #6b7280;
            font-size: 10px;
            margin-top: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .reset-form {
                padding: 2rem;
            }

            .reset-title {
                font-size: 16px;
            }

            .main-container {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .reset-form {
                padding: 1.5rem;
            }

            .reset-title {
                font-size: 14px;
            }

            .form-input {
                font-size: 11px;
                padding: 0.75rem;
            }

            .submit-btn {
                font-size: 11px;
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="reset-container">
            <div class="reset-card">
                <div class="reset-form">
                    <h1 class="reset-title">Reset Password</h1>
                    <p class="reset-subtitle">Set a new password for your account.</p>

                    <!-- User Information -->
                    <div class="user-info">
                        <h4>{{ $user->first_name }} {{ $user->last_name }}</h4>
                        <p>{{ ucfirst($role) }} • ID: {{ $idNumber }}</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <!-- Hidden fields -->
                        <input type="hidden" name="role" value="{{ $role }}">
                        <input type="hidden" name="id_number" value="{{ $idNumber }}">

                        <!-- New Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">New Password (6 digits)</label>
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter your new 6-digit password"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                title="Password must be exactly 6 digits"
                                required>
                            @error('password')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                            <p class="help-text">Password must be exactly 6 digits.</p>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-input"
                                placeholder="Confirm your new 6-digit password"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                title="Password must be exactly 6 digits"
                                required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="submit-btn">
                            Reset Password
                        </button>

                        <!-- Back to Login Link -->
                        <a href="{{ route('login') }}" class="back-link">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Login
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
