window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const staffId = urlParams.get('staffId');
    {
        fetch(`http://localhost:8000/api/dashboard/${staffId}`)
            .then(response => response.json())
            .then(data => {
                const incidentList = document.getElementById('incidentList');
                if (data.length > 0) {
                    data.forEach(incident => {
                        let div = document.createElement('div');
                        div.classList.add('incident-item');
                        div.innerHTML = `
                <h3>Case #${incident.id} - ${incident.problem_type}</h3>
                <p>Severity: ${incident.severity}</p>
                <p>Description: ${incident.description}</p>
                <p>Reporter: ${incident.reporter_name} (${incident.reporter_department})</p>
                <p>Status: ${incident.status}</p>
                <p>Submitted: ${incident.created_at}</p>
              `;
                        incidentList.appendChild(div);
                    });
                } else {
                    incidentList.textContent = 'No incident cases assigned yet.';
                }
            })
            .catch(error => console.error('Error:', error));
    }
});
