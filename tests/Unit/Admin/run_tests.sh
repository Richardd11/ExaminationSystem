#!/bin/bash

# AdminController Unit Test Runner
# This script runs the AdminController unit tests with various options

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default values
VERBOSE=false
COVERAGE=false
COVERAGE_DIR="coverage"
TEST_FILE="AdminControllerTest.php"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to show usage
show_usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -h, --help           Show this help message"
    echo "  -v, --verbose        Run tests with verbose output"
    echo "  -c, --coverage       Generate coverage report"
    echo "  -d, --coverage-dir   Coverage output directory (default: coverage)"
    echo ""
    echo "Examples:"
    echo "  $0                    # Run tests normally"
    echo "  $0 -v                 # Run tests with verbose output"
    echo "  $0 -c                 # Run tests with coverage report"
    echo "  $0 -v -c             # Run tests with verbose output and coverage"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_usage
            exit 0
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -c|--coverage)
            COVERAGE=true
            shift
            ;;
        -d|--coverage-dir)
            COVERAGE_DIR="$2"
            shift 2
            ;;
        *)
            print_error "Unknown option: $1"
            show_usage
            exit 1
            ;;
    esac
done

# Check if we're in the right directory
if [[ ! -f "$TEST_FILE" ]]; then
    print_error "Test file $TEST_FILE not found. Please run this script from the tests/Unit/Admin directory."
    exit 1
fi

# Check if PHPUnit is available
if ! command -v vendor/bin/phpunit &> /dev/null; then
    print_error "PHPUnit not found. Please ensure you're in the project root directory and have run 'composer install'."
    exit 1
fi

# Build PHPUnit command
PHPUNIT_CMD="vendor/bin/phpunit"

if [[ "$VERBOSE" == true ]]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --verbose"
fi

if [[ "$COVERAGE" == true ]]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --coverage-html $COVERAGE_DIR"
    print_status "Coverage report will be generated in: $COVERAGE_DIR"
fi

# Add test file to command
PHPUNIT_CMD="$PHPUNIT_CMD $TEST_FILE"

print_status "Running AdminController unit tests..."
print_status "Command: $PHPUNIT_CMD"
echo ""

# Run the tests
if eval "$PHPUNIT_CMD"; then
    print_success "All tests passed!"
    
    if [[ "$COVERAGE" == true ]]; then
        print_success "Coverage report generated in: $COVERAGE_DIR"
        print_status "Open $COVERAGE_DIR/index.html in your browser to view the coverage report"
    fi
else
    print_error "Some tests failed!"
    exit 1
fi