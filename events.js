
const eventList = document.getElementById("eventList");
const search = document.getElementById("search");
const category = document.getElementById("category");
const sort = document.getElementById("sort");
const previousButton = document.getElementById("previousButton");
const nextButton = document.getElementById("nextButton");
const pageInfo = document.getElementById("pageInfo");
const resultCount = document.getElementById("resultCount");

let allEvents = [];
let currentPage = 1;
const eventsPerPage = 3;

// Fetch data from external JSON file
async function loadEvents() {
    try {
        const response = await fetch("events.json");

        if (!response.ok) {
            throw new Error("Unable to load event data.");
        }

        allEvents = await response.json();
        displayEvents();
    } catch (error) {
        eventList.textContent =
            "Could not load events. Please run this page using Live Server.";
        console.error(error);
    }
}

// Search, filter, sort and display events
function displayEvents() {
    const searchText = search.value.toLowerCase().trim();
    const selectedCategory = category.value;
    const sortValue = sort.value;

    // Search and filter
    let filteredEvents = allEvents.filter(function (event) {
        const matchesSearch =
            event.title.toLowerCase().includes(searchText) ||
            event.description.toLowerCase().includes(searchText);

        const matchesCategory =
            selectedCategory === "All" ||
            event.category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    // Sorting
    filteredEvents.sort(function (a, b) {
        if (sortValue === "dateAsc") {
            return a.date.localeCompare(b.date);
        }

        if (sortValue === "dateDesc") {
            return b.date.localeCompare(a.date);
        }

        if (sortValue === "titleAsc") {
            return a.title.localeCompare(b.title);
        }

        if (sortValue === "titleDesc") {
            return b.title.localeCompare(a.title);
        }

        return 0;
    });

    // Pagination
    const totalEvents = filteredEvents.length;
    const totalPages = Math.ceil(totalEvents / eventsPerPage);

    if (totalPages > 0 && currentPage > totalPages) {
        currentPage = totalPages;
    }

    if (totalPages === 0) {
        currentPage = 1;
    }

    const startIndex = (currentPage - 1) * eventsPerPage;

    const pageEvents = filteredEvents.slice(
        startIndex,
        startIndex + eventsPerPage
    );

    // Dynamic rendering
    eventList.replaceChildren();

    if (pageEvents.length === 0) {
        eventList.textContent = "No events found.";
    } else {
        pageEvents.forEach(function (event) {
            const card = document.createElement("article");
            card.className = "eventCard";

            const title = document.createElement("h3");
            title.textContent = event.title;

            const badge = document.createElement("span");
            badge.className = "category";
            badge.textContent = event.category;

            const date = document.createElement("p");
            date.textContent = "Date: " + event.date;

            const description = document.createElement("p");
            description.textContent = event.description;

            card.append(title, badge, date, description);
            eventList.appendChild(card);
        });
    }

    // Update page information and buttons
    resultCount.textContent = "Total events found: " + totalEvents;
    pageInfo.textContent =
        "Page " + currentPage + " of " + totalPages;

    previousButton.disabled = currentPage === 1;
    nextButton.disabled =
        totalPages === 0 || currentPage >= totalPages;
}

// Event listeners
search.addEventListener("input", function () {
    currentPage = 1;
    displayEvents();
});

category.addEventListener("change", function () {
    currentPage = 1;
    displayEvents();
});

sort.addEventListener("change", function () {
    currentPage = 1;
    displayEvents();
});

previousButton.addEventListener("click", function () {
    if (currentPage > 1) {
        currentPage--;
        displayEvents();
    }
});

nextButton.addEventListener("click", function () {
    currentPage++;
    displayEvents();
});

// Start loading events
loadEvents();
