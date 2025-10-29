+------+---------------+----------------------------------+-----+------+-------------+----------+--------------+----------------+
| iter | benchmark     | subject                          | set | revs | mem_peak    | time_avg | comp_z_value | comp_deviation |
+------+---------------+----------------------------------+-----+------+-------------+----------+--------------+----------------+
| 0    | BenchmarkTest | benchCalculateContrastRatio      |     | 100  | 12,734,960b | 0.290μs  | +0.13σ       | +0.69%         |
| 1    | BenchmarkTest | benchCalculateContrastRatio      |     | 100  | 12,734,960b | 0.290μs  | +0.13σ       | +0.69%         |
| 2    | BenchmarkTest | benchCalculateContrastRatio      |     | 100  | 12,734,960b | 0.260μs  | -1.75σ       | -9.72%         |
| 3    | BenchmarkTest | benchCalculateContrastRatio      |     | 100  | 12,734,960b | 0.310μs  | +1.38σ       | +7.64%         |
| 4    | BenchmarkTest | benchCalculateContrastRatio      |     | 100  | 12,734,960b | 0.290μs  | +0.13σ       | +0.69%         |
| 0    | BenchmarkTest | benchFindClosestAccessibleShade  |     | 100  | 12,734,960b | 0.730μs  | +1.02σ       | +10.61%        |
| 1    | BenchmarkTest | benchFindClosestAccessibleShade  |     | 100  | 12,734,960b | 0.610μs  | -0.73σ       | -7.58%         |
| 2    | BenchmarkTest | benchFindClosestAccessibleShade  |     | 100  | 12,734,960b | 0.550μs  | -1.60σ       | -16.67%        |
| 3    | BenchmarkTest | benchFindClosestAccessibleShade  |     | 100  | 12,734,960b | 0.710μs  | +0.73σ       | +7.58%         |
| 4    | BenchmarkTest | benchFindClosestAccessibleShade  |     | 100  | 12,734,960b | 0.700μs  | +0.58σ       | +6.06%         |
| 0    | BenchmarkTest | benchBulkColorProcessing         |     | 10   | 12,734,960b | 39.000μs | -1.33σ       | -10.71%        |
| 1    | BenchmarkTest | benchBulkColorProcessing         |     | 10   | 12,734,960b | 40.600μs | -0.87σ       | -7.05%         |
| 2    | BenchmarkTest | benchBulkColorProcessing         |     | 10   | 12,734,960b | 45.300μs | +0.46σ       | +3.71%         |
| 3    | BenchmarkTest | benchBulkColorProcessing         |     | 10   | 12,734,960b | 44.600μs | +0.26σ       | +2.11%         |
| 4    | BenchmarkTest | benchBulkColorProcessing         |     | 10   | 12,734,960b | 48.900μs | +1.48σ       | +11.95%        |
| 0    | BenchmarkTest | benchBulkColorProcessingWithTint |     | 10   | 12,734,968b | 56.800μs | -0.40σ       | -2.37%         |
| 1    | BenchmarkTest | benchBulkColorProcessingWithTint |     | 10   | 12,734,968b | 61.100μs | +0.85σ       | +5.02%         |
| 2    | BenchmarkTest | benchBulkColorProcessingWithTint |     | 10   | 12,734,968b | 54.300μs | -1.13σ       | -6.67%         |
| 3    | BenchmarkTest | benchBulkColorProcessingWithTint |     | 10   | 12,734,968b | 63.300μs | +1.49σ       | +8.80%         |
| 4    | BenchmarkTest | benchBulkColorProcessingWithTint |     | 10   | 12,734,968b | 55.400μs | -0.81σ       | -4.78%         |
+------+---------------+----------------------------------+-----+------+-------------+----------+--------------+----------------+

