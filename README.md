# traceable-pdo
Adds trace to your sql statement

[![Code Climate](https://codeclimate.com/github/antonmarin/traceable-pdo/badges/gpa.svg)](https://codeclimate.com/github/antonmarin/traceable-pdo)

[![Test Coverage](https://codeclimate.com/github/antonmarin/traceable-pdo/badges/coverage.svg)](https://codeclimate.com/github/antonmarin/traceable-pdo/coverage)

## Usage

When you use `traceablePDO\PDO` your statement looks like
`SELECT 1 /* eJxT0NdSKM7PTVUoKUpMTlUoLinKzEtX0NIHAF05B60= */`
so to get trace you should just enter in your terminal
`php -r 'echo gzuncompress(base64_decode("eJxT0NdSKM7PTVUoKUpMTlUoLinKzEtX0NIHAF05B60="));'`