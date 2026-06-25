<!DOCTYPE html>
<html lang="en">

<!-- home.php -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2EventHub - Discover Amazing Events</title>
    <link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">
    <link rel="stylesheet" href="<?= url('/fontawesome/css/all.min.css') ?>">
    <style>
        .event-card {
            transition: all 0.3s ease;
            height: 100%;
            /* Forces card to fill the grid row height */
            display: flex;
            /* Turns the card into a flex container */
            flex-direction: column;
            /* Stacks the content vertically */
        }

        .event-card .card-body {
            flex-grow: 1;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .category-btn.active {
            background-color: #4f46e5;
            color: white;
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-box {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <?php component("/components/header", ['active_page' => 'explore']); ?>

    <!-- Hero Section -->
    <section class="hero-section text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Explore Events at University
            </h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Stay updated with campus events, workshops, and activities organized by various departments.

            </p>
        </div>
    </section>

    <!-- Events Section -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">All Events</h2>
            </div>

            <!-- Event Cards Section -->
            <div id="events-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Cards will be inserted here dynamically -->
                <?php
                // $events provided by controller before calling this view
                $html = "";
                foreach ($events as $event) {
                    $html .= component('public/components/eventCard', $event, true);
                }
                echo $html;
                ?>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-12">
                <p class="text-gray-600 text-sm" id="page-info"> <?= $pagination['showing_text'] ?> </p>
                <div class="flex space-x-2" id="pagination">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>&resultCount=<?= $pagination['per_page'] ?>"
                            class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-100">
                            Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <a href="?page=<?= $i ?>&resultCount=<?= $pagination['per_page'] ?>"
                            class="px-3 py-1 rounded border <?= $i == $pagination['current_page'] ? 'bg-indigo-500 text-white' : 'bg-white hover:bg-gray-100 text-gray-700 border-gray-300' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>&resultCount=<?= $pagination['per_page'] ?>"
                            class="px-3 py-1 rounded border border-gray-300 bg-white hover:bg-gray-100">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section not required (uncomment whenever required) -->
    <?php // include_once(__DIR__ . "/components/newsletter.php"); 
    ?>

    <!-- Footer -->
    <?php component("components/footer"); ?>


</body>

</html>