#!/bin/bash

# CRUD Package Test Runner with Docker
#
# Optimized testing with pre-built image and code coverage

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_usage() {
    echo -e "${BLUE}🧪 CRUD Package Test Runner${NC}"
    echo "================================"
    echo ""
    echo "Usage: $0 [OPTION] [PHPUNIT_ARGS...]"
    echo ""
    echo "Options:"
    echo "  test             Run tests with code coverage (default)"
    echo "  interactive      Open interactive shell for debugging"
    echo "  clean            Clean up Docker volumes and images"
    echo "  help             Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0               # Run all tests (default action)"
    echo "  $0 test          # Same as above"
    echo "  $0 test --filter SomeTest  # Run specific test class"
    echo "  $0 test --filter test_method  # Run specific test method"
    echo "  $0 interactive   # Debug tests interactively"
}

run_tests() {
    echo -e "${GREEN}🚀 Running CRUD tests with coverage...${NC}"

    # Build the phpunit command with arguments
    local phpunit_cmd="./vendor/bin/phpunit --colors=always --coverage-html coverage/html --coverage-clover coverage/clover.xml"

    if [ $# -gt 0 ]; then
        phpunit_cmd="$phpunit_cmd $@"
    fi

    docker compose -f docker-compose.test.yml run --rm -T crud-test bash -c "
        echo '🧪 CRUD Package Tests with Coverage'
        echo '===================================='
        echo 'ℹ️  Using optimized pre-built image'
        echo ''
        echo '📦 Syncing dependencies...'
        composer install --no-interaction --prefer-dist --optimize-autoloader --dev --quiet
        echo '🏃 Running tests with coverage...'
        $phpunit_cmd
        echo ''
        echo '📈 Coverage reports generated:'
        echo '  HTML: coverage/html/index.html'
        echo '  XML:  coverage/clover.xml'
    "
    return $?
}

run_interactive() {
    echo -e "${GREEN}� Opening interactive test shell...${NC}"
    docker compose -f docker-compose.test.yml run --rm crud-test-interactive
    return $?
}

clean_docker() {
    echo -e "${YELLOW}🧹 Cleaning up Docker resources...${NC}"

    # Stop and remove containers
    docker compose -f docker-compose.test.yml down --volumes 2>/dev/null || true

    # Remove the test image
    docker rmi crud-test-image:latest 2>/dev/null || true

    # Remove unused volumes
    docker volume prune -f 2>/dev/null || true

    echo -e "${GREEN}✅ Cleanup completed${NC}"
    return 0
}

run_interactive() {
    echo -e "${GREEN}🐚 Opening interactive test shell...${NC}"
    docker compose -f docker-compose.test.yml run --rm crud-test-interactive
    return $?
}

# Main script logic
case "${1:-test}" in
    "test"|"")
        # Shift the first argument if it's "test"
        if [ "$1" = "test" ]; then
            shift
        fi

        # If next argument is "--", shift it too
        if [ "$1" = "--" ]; then
            shift
        fi

        run_tests "$@"
        exit $?
        ;;
    "interactive")
        run_interactive
        exit $?
        ;;
    "clean")
        clean_docker
        exit $?
        ;;
    "help"|"-h"|"--help")
        print_usage
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Unknown option: $1${NC}"
        echo ""
        print_usage
        exit 1
        ;;
esac
