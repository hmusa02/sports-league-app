// API Configuration
const API_BASE = "http://localhost:8888/sports-league-app/backend/";

// Event Types Configuration
const EVENT_TYPES = {
  GOAL: "Goal",
  ASSIST: "Assist",
  YELLOW_CARD: "Yellow Card",
  RED_CARD: "Red Card",
  SUBSTITUTION: "Substitution",
};

// Badge Classes for Event Types
function getEventBadgeClass(eventType) {
  switch (eventType) {
    case EVENT_TYPES.GOAL:
      return "bg-success";
    case EVENT_TYPES.ASSIST:
      return "bg-primary";
    case EVENT_TYPES.YELLOW_CARD:
      return "bg-warning";
    case EVENT_TYPES.RED_CARD:
      return "bg-danger";
    case EVENT_TYPES.SUBSTITUTION:
      return "bg-info";
    default:
      return "bg-secondary";
  }
}
