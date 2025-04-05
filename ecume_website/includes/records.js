
    // function fetchFilteredRecords() {
    //     const memberId = document.getElementById('member_id').value;
    //     const type = document.getElementById('type').value;
    //     const startDate = document.getElementById('start_date').value;
    //     const endDate = document.getElementById('end_date').value;

    //     fetch(`admin_records_filter.php?member_id=${memberId}&type=${type}&start_date=${startDate}&end_date=${endDate}`)
    //         .then(response => response.json())
    //         .then(data => {
    //             const recordsBody = document.getElementById('recordsBody');
    //             recordsBody.innerHTML = ''; // Clear existing rows

    //             if (data.length === 0) {
    //                 recordsBody.innerHTML = '<tr><td colspan="4">No records found</td></tr>';
    //                 return;
    //             }

    //             data.forEach(record => {
    //                 const row = `<tr>
    //                     <td>${record.full_name}</td>
    //                     <td>${record.type.replace('_', ' ')}</td>
    //                     <td>₦${parseFloat(record.amount).toLocaleString()}</td>
    //                     <td>${record.date}</td>
    //                 </tr>`;
    //                 recordsBody.innerHTML += row;
    //             });
    //         })
    //         .catch(error => console.error('Error fetching records:', error));
    // }

    // document.addEventListener('DOMContentLoaded', () => {
    //     document.getElementById('filterForm').addEventListener('submit', function(event) {
    //         event.preventDefault();
    //         fetchFilteredRecords();
    //     });

    //     // Fetch records on page load
    //     fetchFilteredRecords();
    // });

    fetch("admin_record_filter.php")
    .then(response => response.json())
    .then(data => {
        console.log(data); // Process the data
    })
    .catch(error => console.error("Error fetching data:", error));
