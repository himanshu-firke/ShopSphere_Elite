/**
 * Backend Connection Test Utility
 * Use this to test if React frontend can connect to Laravel backend
 */

import api from '../services/api';

interface TestResult {
    test: string;
    status: 'success' | 'error';
    message: string;
    data?: any;
}

class BackendConnectionTest {
    private results: TestResult[] = [];

    // Test basic API connection
    async testApiConnection(): Promise<TestResult> {
        try {
            const response = await fetch('http://localhost:8000/api/health', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                return {
                    test: 'API Connection',
                    status: 'success',
                    message: 'Successfully connected to Laravel backend',
                    data: { status: response.status }
                };
            } else {
                return {
                    test: 'API Connection',
                    status: 'error',
                    message: `HTTP ${response.status}: ${response.statusText}`,
                };
            }
        } catch (error) {
            return {
                test: 'API Connection',
                status: 'error',
                message: `Connection failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
            };
        }
    }

    // Test products endpoint
    async testProductsEndpoint(): Promise<TestResult> {
        try {
            const response = await fetch('http://localhost:8000/api/products', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                const data = await response.json();
                return {
                    test: 'Products Endpoint',
                    status: 'success',
                    message: `Products endpoint working. Found ${data.data?.length || 0} products`,
                    data: { count: data.data?.length || 0 }
                };
            } else {
                return {
                    test: 'Products Endpoint',
                    status: 'error',
                    message: `HTTP ${response.status}: ${response.statusText}`,
                };
            }
        } catch (error) {
            return {
                test: 'Products Endpoint',
                status: 'error',
                message: `Products endpoint failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
            };
        }
    }

    // Test cart endpoint (requires authentication)
    async testCartEndpoint(): Promise<TestResult> {
        try {
            const response = await fetch('http://localhost:8000/api/cart', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            if (response.status === 401) {
                return {
                    test: 'Cart Endpoint',
                    status: 'success',
                    message: 'Cart endpoint working (authentication required as expected)',
                };
            } else if (response.ok) {
                const data = await response.json();
                return {
                    test: 'Cart Endpoint',
                    status: 'success',
                    message: 'Cart endpoint working and accessible',
                    data: data
                };
            } else {
                return {
                    test: 'Cart Endpoint',
                    status: 'error',
                    message: `HTTP ${response.status}: ${response.statusText}`,
                };
            }
        } catch (error) {
            return {
                test: 'Cart Endpoint',
                status: 'error',
                message: `Cart endpoint failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
            };
        }
    }

    // Run all tests
    async runAllTests(): Promise<TestResult[]> {
        console.log('🧪 Starting Backend Connection Tests...\n');

        const tests = [
            this.testApiConnection(),
            this.testProductsEndpoint(),
            this.testCartEndpoint(),
        ];

        this.results = await Promise.all(tests);

        // Log results to console
        this.results.forEach(result => {
            const icon = result.status === 'success' ? '✅' : '❌';
            console.log(`${icon} ${result.test}: ${result.message}`);
            if (result.data) {
                console.log('   Data:', result.data);
            }
        });

        const successCount = this.results.filter(r => r.status === 'success').length;
        const totalCount = this.results.length;

        console.log(`\n📊 Test Summary: ${successCount}/${totalCount} tests passed`);

        if (successCount === totalCount) {
            console.log('🎉 All tests passed! Your backend integration is working correctly.');
        } else {
            console.log('⚠️  Some tests failed. Check the Laravel backend server and database connection.');
        }

        return this.results;
    }

    // Get test results
    getResults(): TestResult[] {
        return this.results;
    }
}

// Export singleton instance
export const backendTest = new BackendConnectionTest();

// Helper function to run tests from browser console
export const testBackend = () => {
    return backendTest.runAllTests();
};

// Auto-run tests in development mode
if (process.env.NODE_ENV === 'development') {
    // Add to window for easy access in browser console
    (window as any).testBackend = testBackend;
    console.log('💡 Tip: Run testBackend() in browser console to test backend connection');
}
