#!/bin/bash
set -euo pipefail

JENKINS_BASE_URL="http://${SPRYKER_SCHEDULER_HOST}:${SPRYKER_SCHEDULER_PORT}"

# Fetch all jobs (no crumb needed for GET)
JOBS_RESPONSE=$(curl -sg "${JENKINS_BASE_URL}/api/json/jobs?pretty=true&tree=jobs[name]")

# Parse job names — pretty-printed JSON has spaces around ":"
JOBS=$(echo "$JOBS_RESPONSE" | grep '"name"' | sed 's/.*"name" *: *"\(.*\)".*/\1/')

if [[ -z "$JOBS" ]]; then
    echo "No jobs found."
    exit 0
fi

echo "Found jobs:"
echo "$JOBS"
echo ""

COOKIE_JAR=$(mktemp)

# Fetch CSRF crumb — save session cookie, Jenkins ties the crumb to the session
CRUMB_RESPONSE=$(curl -s -c "$COOKIE_JAR" "${JENKINS_BASE_URL}/crumbIssuer/api/json")
CRUMB=$(echo "$CRUMB_RESPONSE" | grep -o '"crumb":"[^"]*"' | cut -d'"' -f4)
CRUMB_FIELD=$(echo "$CRUMB_RESPONSE" | grep -o '"crumbRequestField":"[^"]*"' | cut -d'"' -f4)

if [[ -z "$CRUMB" || -z "$CRUMB_FIELD" ]]; then
    rm -f "$COOKIE_JAR"
    echo "ERROR: Failed to fetch CSRF crumb" >&2
    exit 1
fi

echo "Fetched CSRF crumb: ${CRUMB_FIELD}=${CRUMB}"
echo ""

# Delete each job — send the session cookie so Jenkins accepts the crumb
while IFS= read -r JOB_NAME; do
    echo "Deleting: ${JOB_NAME}"
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST -b "$COOKIE_JAR" -H "${CRUMB_FIELD}: ${CRUMB}" "${JENKINS_BASE_URL}/job/${JOB_NAME}/doDelete")
    echo "  -> HTTP ${HTTP_STATUS}"
done <<< "$JOBS"

rm -f "$COOKIE_JAR"

echo "Done."
