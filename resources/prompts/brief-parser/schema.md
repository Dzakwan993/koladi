{  
  "summary": {  
    "project_name": "Project name or null if cannot be reasonably inferred (string/null)",  
    "project_description": "Concise summary (1-3 sentences) of the project (string)",  
    "deliverables": ["Array of extracted deliverables, e.g., 'Landing Page', 'Logo', or empty array if none (array of strings)"],  
    "main_deadline": "Project main deadline, keeping relative dates exactly as written, or null if not found (string/null)"  
  },  
  "decisions": [
    {
      "title": "Decision title (string)",
      "sources": ["Array of source document names supporting this decision, e.g. 'Client Brief.pdf' (array of strings)"]
    }
  ],
  "tasks": [  
    {  
      "title": "Task title (string)",  
      "description": "Short task description (string)",  
      "priority": "low, medium, high, urgent, or null if no urgency evidence exists (string/null)",  
      "deadline": "Deadline, keeping relative dates exactly as written, or null if not found (string/null)"  
    }  
  ],  
  "missing_information": ["Array of genuinely missing and important information categories/items, or empty array (array of strings)"],  
  "clarification_questions": ["Array of specific questions generated from missing information, or empty array (array of strings)"]  
}
