import { Link, usePage, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PageHeader } from '@/components/PageHeader';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import AppLayout from '@/Layouts/AppLayout';

interface Contact {
  id: number;
  name: string;
  email: string;
  phone: string;
  title: string;
}

interface Deal {
  id: number;
  title: string;
  amount: number;
  stage: string;
  expected_close_date: string;
}

interface Company {
  id: number;
  name: string;
  email: string;
  phone: string;
  website: string;
  industry: string;
  employee_count: number;
  full_address: string;
  notes: string;
}

interface Props {
  company: Company;
  contacts: Contact[];
  deals: Deal[];
}

export default function CompaniesShow({ company, contacts, deals }: Props) {
  const { flash: pageFlash } = usePage().props as { flash?: { success?: string } };

  const handleDelete = () => {
    if (confirm(`Are you sure you want to delete "${company.name}"? This action cannot be undone.`)) {
      router.delete(`/companies/${company.id}`, {
        onSuccess: () => router.visit('/companies'),
      });
    }
  };

  return (
    <AppLayout title={company.name}>
      {/* Success Message */}
      {pageFlash?.success && (
        <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {pageFlash.success}
        </div>
      )}

      <PageHeader title={company.name} description="Company Details">
        <div className="space-x-2">
          <Link href={`/companies/${company.id}/edit`}>
            <Button>Edit</Button>
          </Link>
          <Button variant="destructive" onClick={handleDelete}>
            Delete Company
          </Button>
          <Link href="/companies">
            <Button variant="outline">Back to Companies</Button>
          </Link>
        </div>
      </PageHeader>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {/* Company Information Card */}
        <div className="md:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle className="text-lg">Company Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {company.email && (
                <div>
                  <p className="text-sm text-gray-600">Email</p>
                  <a
                    href={`mailto:${company.email}`}
                    className="text-blue-600 hover:underline"
                  >
                    {company.email}
                  </a>
                </div>
              )}
              {company.phone && (
                <div>
                  <p className="text-sm text-gray-600">Phone</p>
                  <p className="font-medium">{company.phone}</p>
                </div>
              )}
              {company.website && (
                <div>
                  <p className="text-sm text-gray-600">Website</p>
                  <a
                    href={company.website}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-blue-600 hover:underline"
                  >
                    {company.website}
                  </a>
                </div>
              )}
              {company.industry && (
                <div>
                  <p className="text-sm text-gray-600">Industry</p>
                  <p className="font-medium">{company.industry}</p>
                </div>
              )}
              {company.employee_count && (
                <div>
                  <p className="text-sm text-gray-600">Employee Count</p>
                  <p className="font-medium">{company.employee_count}</p>
                </div>
              )}
              {company.full_address && (
                <div>
                  <p className="text-sm text-gray-600">Address</p>
                  <p className="font-medium text-sm">{company.full_address}</p>
                </div>
              )}
              {company.notes && (
                <div>
                  <p className="text-sm text-gray-600">Notes</p>
                  <p className="text-sm">{company.notes}</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Contacts and Deals Summary */}
        <div className="md:col-span-2 space-y-6">
          {/* Contacts Section */}
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle className="text-lg">
                  Contacts {contacts.length > 0 && `(${contacts.length})`}
                </CardTitle>
                <Link href={`/contacts/create?company_id=${company.id}`}>
                  <Button size="sm">Add Contact</Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {contacts.length > 0 ? (
                <div className="space-y-3">
                  {contacts.map((contact) => (
                    <Link
                      key={contact.id}
                      href={`/contacts/${contact.id}`}
                      className="block p-3 border rounded hover:bg-gray-50"
                    >
                      <p className="font-medium text-blue-600">{contact.name}</p>
                      <p className="text-sm text-gray-600">{contact.title}</p>
                      <p className="text-sm text-gray-500">{contact.email}</p>
                    </Link>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500">No contacts yet</p>
              )}
            </CardContent>
          </Card>

          {/* Deals Section */}
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle className="text-lg">
                  Deals {deals.length > 0 && `(${deals.length})`}
                </CardTitle>
                <Link href={`/deals/create?company_id=${company.id}`}>
                  <Button size="sm">Add Deal</Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {deals.length > 0 ? (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Title</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Stage</TableHead>
                        <TableHead>Close Date</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {deals.map((deal) => (
                        <TableRow key={deal.id}>
                          <TableCell>
                            <Link href={`/deals/${deal.id}`}>
                              <span className="text-blue-600 hover:underline">
                                {deal.title}
                              </span>
                            </Link>
                          </TableCell>
                          <TableCell>
                            ${deal.amount?.toLocaleString()}
                          </TableCell>
                          <TableCell>
                            <span className="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                              {deal.stage}
                            </span>
                          </TableCell>
                          <TableCell>
                            {new Date(deal.expected_close_date).toLocaleDateString()}
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              ) : (
                <p className="text-gray-500">No deals yet</p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </AppLayout>
  );
}

