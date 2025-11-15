import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { PageHeader } from '@/components/PageHeader';
import { Pagination } from '@/components/Pagination';
import AppLayout from '@/Layouts/AppLayout';
import { useState, useEffect, useRef } from 'react';

interface Company {
  id: number;
  name: string;
  email: string;
  phone: string;
  industry: string;
  city: string;
  contacts_count: number;
  deals_count: number;
}

interface Props {
  companies: {
    data: Company[];
    links: any;
    meta: any;
  };
  filters: {
    search?: string;
    industry?: string;
  };
}

export default function CompaniesIndex({ companies, filters }: Props) {
  const { flash } = usePage().props as { flash?: { success?: string } };
  const [search, setSearch] = useState(filters.search || '');
  const [industry, setIndustry] = useState(filters.industry || '');
  const initialMount = useRef(true);

  // Debounced search logic - waits 300ms after user stops typing
  useEffect(() => {
    // Skip initial render to prevent immediate load
    if (initialMount.current) {
      initialMount.current = false;
      return;
    }

    const delayDebounceFn = setTimeout(() => {
      router.get(
        '/companies',
        { search: search, industry: industry },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true, // Replace history entry instead of adding new one
        }
      );
    }, 300); // Wait 300ms after typing stops

    return () => clearTimeout(delayDebounceFn);
  }, [search, industry]);

  const handleClearFilters = () => {
    setSearch('');
    setIndustry('');
    router.get('/companies', {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };
  return (
    <AppLayout title="Companies">
      {/* Success Message */}
      {flash?.success && (
        <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {flash.success}
        </div>
      )}

      <PageHeader 
        title="Companies"
        description="Manage all companies in your CRM"
      >
        <Link href="/companies/create">
          <Button>Add Company</Button>
        </Link>
      </PageHeader>

      {/* Search and Filter Section */}
      <Card className="mb-4">
        <CardContent className="pt-6">
          <div className="flex items-center gap-4 flex-wrap">
            {/* Search Input */}
            <div className="flex-1 min-w-[200px]">
              <Input
                type="text"
                placeholder="Search companies by name..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>

            {/* Industry Filter */}
            <div>
              <select
                value={industry}
                onChange={(e) => setIndustry(e.target.value)}
                className="px-3 py-2 border rounded-md shadow-sm"
              >
                <option value="">All Industries</option>
                <option value="Technology">Technology</option>
                <option value="Finance">Finance</option>
                <option value="Retail">Retail</option>
                <option value="Healthcare">Healthcare</option>
                {/* Add more industry options based on your data */}
              </select>
            </div>

            {/* Clear Filters Button */}
            {(search || industry) && (
              <Button variant="outline" onClick={handleClearFilters}>
                Clear Filters
              </Button>
            )}
          </div>
        </CardContent>
      </Card>

      <div className="space-y-4">
        <div className="rounded-lg border overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow className="bg-gray-50">
                <TableHead className="font-semibold">Company Name</TableHead>
                <TableHead className="font-semibold">Email</TableHead>
                <TableHead className="font-semibold">Phone</TableHead>
                <TableHead className="font-semibold">Industry</TableHead>
                <TableHead className="font-semibold text-center">Contacts</TableHead>
                <TableHead className="font-semibold text-center">Deals</TableHead>
                <TableHead className="font-semibold">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {companies.data.map((company) => (
                <TableRow key={company.id} className="hover:bg-gray-50">
                  <TableCell>
                    <Link href={`/companies/${company.id}`}>
                      <span className="text-blue-600 hover:underline cursor-pointer">
                        {company.name}
                      </span>
                    </Link>
                  </TableCell>
                  <TableCell>{company.email || '—'}</TableCell>
                  <TableCell>{company.phone || '—'}</TableCell>
                  <TableCell>{company.industry || '—'}</TableCell>
                  <TableCell className="text-center">
                    {company.contacts_count}
                  </TableCell>
                  <TableCell className="text-center">
                    {company.deals_count}
                  </TableCell>
                  <TableCell className="space-x-2">
                    <Link href={`/companies/${company.id}`}>
                      <Button variant="ghost" size="sm">View</Button>
                    </Link>
                    <Link href={`/companies/${company.id}/edit`}>
                      <Button variant="outline" size="sm">Edit</Button>
                    </Link>
                    <Button
                      variant="destructive"
                      size="sm"
                      onClick={() => {
                        if (confirm(`Are you sure you want to delete "${company.name}"?`)) {
                          router.delete(`/companies/${company.id}`, {
                            preserveScroll: true,
                          });
                        }
                      }}
                    >
                      Delete
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>

        {companies.data.length === 0 && (
          <div className="text-center py-12">
            <p className="text-gray-500 mb-4">No companies yet.</p>
            <Link href="/companies/create">
              <Button>Create First Company</Button>
            </Link>
          </div>
        )}

        <Pagination links={companies.links} meta={companies.meta} />
      </div>
    </AppLayout>
  );
}

