<?php
include '../utils.php'; // Make sure to include your utils file
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_GET['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    $result = resetUserPassword($conn, $token, $newPassword);

    echo '<script>alert("' . addslashes($result['message']) . '");</script>';

    if ($result['success']) {
        echo '<script>window.location.href = "login.php";</script>';
        exit;
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="../../assets/css/tailwind.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>Reset Password - Knot.Us</title>
</head>

<body>
<section class="bg-gray-50 dark:bg-gray-900">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0" data-aos="fade-up"
         data-aos-duration="1500">
        <a href="../index.html" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg" alt="logo">
            Flowbite
        </a>
        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    Reset your password
                </h1>
                <form class="space-y-4 md:space-y-6" method="POST">
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                        <input type="password" name="password" id="password" placeholder="New password"
                               class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-pink-600 focus:border-pink-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-pink-500 dark:focus:border-pink-500"
                               required="">
                    </div>
                    <button type="submit"
                            class="w-full text-white bg-pink-600 hover:bg-pink-700 focus:ring-4 focus:outline-none focus:ring-pink-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-pink-600 dark:hover:bg-pink-700 dark:focus:ring-pink-800">
                        Reset password
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script src="../../node_modules/flowbite/dist/flowbite.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>
</body>

</html>
