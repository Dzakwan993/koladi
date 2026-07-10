Output JSON must strictly follow this schema structure:

{
    "project": {
        "name": "Project Name (string)",
        "goal": "Main goal of the project (string)",
        "deliverables": "Key deliverables, joined by comma if multiple (string)",
        "deadline": "Project deadline, formatted as YYYY-MM-DD or null if not found (string/null)",
        "confidence_level": "AI Confidence score as percentage based on how complete the brief information is, e.g. 94 (integer)"
    },
    "tasks": [
        {
            "title": "Task title (string)",
            "description": "Short task description (string)",
            "priority": "low, medium, high, or urgent (string)",
            "deadline": "Deadline formatted as YYYY-MM-DD or null if not found (string/null)",
            "suggested_owner": "Name or initials of suggested owner from document context, or null (string/null)"
        }
    ],
    "clarification_questions": [
        "Question 1 (string)",
        "Question 2 (string)"
    ]
}
