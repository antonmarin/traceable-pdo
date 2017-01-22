# traceable-pdo

Adds trace to your sql statement

[![Build Status](https://travis-ci.org/antonmarin/traceable-pdo.svg?branch=master)](https://travis-ci.org/antonmarin/traceable-pdo)
[![Latest Stable Version](https://poser.pugx.org/antonmarin/traceable-pdo/v/stable)](https://packagist.org/packages/antonmarin/traceable-pdo)
[![License](https://poser.pugx.org/antonmarin/traceable-pdo/license)](https://packagist.org/packages/antonmarin/traceable-pdo)

[![Code Climate](https://codeclimate.com/github/antonmarin/traceable-pdo/badges/gpa.svg)](https://codeclimate.com/github/antonmarin/traceable-pdo)
[![Test Coverage](https://codeclimate.com/github/antonmarin/traceable-pdo/badges/coverage.svg)](https://codeclimate.com/github/antonmarin/traceable-pdo/coverage)
[![Total Downloads](https://poser.pugx.org/antonmarin/traceable-pdo/downloads)](https://packagist.org/packages/antonmarin/traceable-pdo)

## Usage

When you use `traceablePDO\PDO` your statement looks like
`SELECT 1 /* eJxT0NdSKM7PTVUoKUpMTlUoLinKzEtX0NIHAF05B60= */`
so to get trace you should just enter in your terminal
`php -r 'echo gzuncompress(base64_decode("eJxT0NdSKM7PTVUoKUpMTlUoLinKzEtX0NIHAF05B60="));'`